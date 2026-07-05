<?php

namespace App\Console\Commands;

use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeSetup;
use App\Models\Institution;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyFeeInvoices extends Command
{
    protected $signature = 'fee:monthly-generate {--month=} {--institution=}';
    protected $description = 'প্রতি মাসে Fee Setup অনুযায়ী (Monthly/Yearly/One-time মিলিয়ে) প্রতিটা Active Student-এর জন্য Auto Invoice Generate করে';

    public function handle(): int
    {
        $monthInput = $this->option('month'); // e.g. "2026-07" na dile current month
        $invoiceDate = $monthInput
            ? Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth()
            : now()->startOfMonth();

        $monthStart = $invoiceDate->copy()->startOfMonth();
        $monthEnd = $invoiceDate->copy()->endOfMonth();
        $currentMonthNumber = $invoiceDate->month;
        $currentYear = $invoiceDate->year;

        $institutionsQuery = Institution::query()->where('status', true);

        if ($this->option('institution')) {
            $institutionsQuery->where('id', $this->option('institution'));
        }

        $institutions = $institutionsQuery->get();

        $totalGenerated = 0;
        $totalSkipped = 0;

        foreach ($institutions as $institution) {

            $this->info("Institution: {$institution->name} — processing...");

            $feeSetups = FeeSetup::query()
                ->where('institution_id', $institution->id)
                ->where('status', true)
                ->get()
                ->groupBy('class_id');

            if ($feeSetups->isEmpty()) {
                $this->warn("  -> Kono Fee Setup nai, skip kora hocche.");
                continue;
            }

            $students = Student::query()
                ->where('institution_id', $institution->id)
                ->whereHas('user', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();

            foreach ($students as $student) {

                $classFeeSetups = $feeSetups->get($student->class_id);

                if (! $classFeeSetups || $classFeeSetups->isEmpty()) {
                    $totalSkipped++;
                    continue;
                }

                // ── এই মাসে কোন কোন Fee Setup আসলে Invoice-এ যাবে (Frequency অনুযায়ী ফিল্টার) ──
                $applicableFeeSetups = $classFeeSetups->filter(function ($feeSetup) use (
                    $student,
                    $currentMonthNumber,
                    $currentYear
                ) {
                    return $this->isFeeSetupApplicable($feeSetup, $student->id, $currentMonthNumber, $currentYear);
                });

                if ($applicableFeeSetups->isEmpty()) {
                    $totalSkipped++;
                    continue;
                }

                // এই মাসে ইতিমধ্যে Invoice হয়ে গেছে কিনা (Duplicate Run প্রতিরোধ)
                $alreadyExists = FeeInvoice::query()
                    ->where('institution_id', $institution->id)
                    ->where('student_id', $student->id)
                    ->whereBetween('invoice_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->exists();

                if ($alreadyExists) {
                    $totalSkipped++;
                    continue;
                }

                DB::beginTransaction();

                try {
                    $invoiceNo = $this->generateInvoiceNumber($institution->id, $invoiceDate);

                    $subtotal = $applicableFeeSetups->sum('amount');

                    $invoice = FeeInvoice::create([
                        'institution_id' => $institution->id,
                        'student_id' => $student->id,
                        'invoice_no' => $invoiceNo,
                        'subtotal' => $subtotal,
                        'discount_amount' => 0,
                        'fine_amount' => 0,
                        'total_amount' => $subtotal,
                        'paid_amount' => 0,
                        'due_amount' => $subtotal,
                        'invoice_date' => $monthStart->toDateString(),
                        'due_date' => $monthStart->copy()->addDays(15)->toDateString(),
                        'payment_status' => 'unpaid',
                        'status' => true,
                    ]);

                    foreach ($applicableFeeSetups as $feeSetup) {
                        FeeInvoiceItem::create([
                            'institution_id' => $institution->id,
                            'fee_invoice_id' => $invoice->id,
                            'fee_setup_id' => $feeSetup->id,
                            'base_amount' => $feeSetup->amount,
                            'fine_amount' => 0,
                            'discount_amount' => 0,
                            'total_amount' => $feeSetup->amount,
                        ]);
                    }

                    activity()
                        ->tap(function ($activity) use ($institution) {
                            $activity->institution_id = $institution->id;
                        })
                        ->performedOn($invoice)
                        ->log("Monthly invoice auto-generated for student #{$student->id}");

                    DB::commit();

                    $totalGenerated++;

                } catch (\Throwable $e) {
                    DB::rollBack();

                    report($e);

                    $this->error("  -> Student #{$student->id} er invoice generate hoyni: {$e->getMessage()}");
                }
            }
        }

        $this->info("Shesh! Total Generated: {$totalGenerated}, Total Skipped: {$totalSkipped}");

        return self::SUCCESS;
    }

    /**
     * এই Fee Setup-টা এই Student-এর জন্য এই মাসে Invoice-এ যাবে কিনা,
     * Frequency (monthly/yearly/one_time) অনুযায়ী চেক করে।
     */
    protected function isFeeSetupApplicable(
        FeeSetup $feeSetup,
        int $studentId,
        int $currentMonthNumber,
        int $currentYear
    ): bool {
        return match ($feeSetup->frequency) {

            // Monthly — সবসময় Applicable
            'monthly' => true,

            // Yearly — শুধু billing_month মিললে, আর এই বছরে আগে না হয়ে থাকলে
            'yearly' => $feeSetup->billing_month === $currentMonthNumber
                && ! $this->alreadyInvoiced($feeSetup->id, $studentId, $currentYear),

            // One Time — শুধু billing_month মিললে, আর Lifetime-এ আগে কখনো না হয়ে থাকলে
            'one_time' => $feeSetup->billing_month === $currentMonthNumber
                && ! $this->alreadyInvoiced($feeSetup->id, $studentId, null),

            default => false,
        };
    }

    /**
     * এই Student-এর জন্য এই Fee Setup-এ আগে কখনো Invoice হয়েছে কিনা চেক করে।
     * $year দেওয়া থাকলে শুধু সেই বছরের মধ্যে চেক হবে (Yearly-এর জন্য),
     * $year null দিলে Lifetime চেক হবে (One Time-এর জন্য)।
     */
    protected function alreadyInvoiced(int $feeSetupId, int $studentId, ?int $year): bool
    {
        return FeeInvoiceItem::query()
            ->where('fee_setup_id', $feeSetupId)
            ->whereHas('invoice', function ($query) use ($studentId, $year) {
                $query->where('student_id', $studentId);

                if ($year !== null) {
                    $query->whereYear('invoice_date', $year);
                }
            })
            ->exists();
    }

    protected function generateInvoiceNumber(int $institutionId, Carbon $invoiceDate): string
    {
        $prefix = 'INV-' . $invoiceDate->format('Ym') . '-';

        return DB::transaction(function () use ($institutionId, $prefix) {
            $last = FeeInvoice::where('institution_id', $institutionId)
                ->where('invoice_no', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextNumber = 1;

            if ($last) {
                $lastNumber = (int) substr($last->invoice_no, strlen($prefix));
                $nextNumber = $lastNumber + 1;
            }

            return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        });
    }
}
<?php

namespace App\Console\Commands;

use App\Models\FeeFine;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeSetup;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentFine;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyFeeInvoices extends Command
{
    protected $signature = 'fee:monthly-generate {--month=} {--institution=}';
    protected $description = 'প্রতি মাসে Fee Setup অনুযায়ী (Monthly/Yearly/One-time মিলিয়ে) প্রতিটা Active Student-এর জন্য Auto Invoice Generate করে, সাথে Late Fee ও Student Fine যোগ করে';

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

                    // ── Late Fee (FeeFine) হিসাব — প্রতিটা applicable Fee Setup-এর জন্য ──
                    $lateFeeMap = [];   // [fee_setup_id => ['amount' => x, 'source_item_id' => y]]
                    $lateFeeTotal = 0;

                    foreach ($applicableFeeSetups as $feeSetup) {
                        $lateFine = $this->calculateLateFee($feeSetup, $student->id, $invoiceDate);

                        if ($lateFine['amount'] > 0) {
                            $lateFeeMap[$feeSetup->id] = $lateFine;
                            $lateFeeTotal += $lateFine['amount'];
                        }
                    }

                    // ── Student Fine (Absent/Indiscipline etc.) — Pending সব যোগ হবে ──
                    $pendingStudentFines = StudentFine::query()
                        ->where('institution_id', $institution->id)
                        ->where('student_id', $student->id)
                        ->where('status', 'pending')
                        ->get();

                    $studentFineTotal = (float) $pendingStudentFines->sum('amount');

                    $fineAmount = $lateFeeTotal + $studentFineTotal;
                    $totalAmount = $subtotal + $fineAmount;

                    $invoice = FeeInvoice::create([
                        'institution_id' => $institution->id,
                        'student_id' => $student->id,
                        'invoice_no' => $invoiceNo,
                        'subtotal' => $subtotal,
                        'discount_amount' => 0,
                        'fine_amount' => $fineAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => 0,
                        'due_amount' => $totalAmount,
                        'invoice_date' => $monthStart->toDateString(),
                        'due_date' => $monthStart->copy()->addDays(15)->toDateString(),
                        'payment_status' => 'unpaid',
                        'status' => true,
                    ]);

                    foreach ($applicableFeeSetups as $feeSetup) {
                        $itemFine = $lateFeeMap[$feeSetup->id]['amount'] ?? 0;

                        FeeInvoiceItem::create([
                            'institution_id' => $institution->id,
                            'fee_invoice_id' => $invoice->id,
                            'fee_setup_id' => $feeSetup->id,
                            'base_amount' => $feeSetup->amount,
                            'fine_amount' => $itemFine,
                            'discount_amount' => 0,
                            'total_amount' => $feeSetup->amount + $itemFine,
                        ]);
                    }

                    // পুরনো overdue item-গুলোতে মার্ক করা হচ্ছে যে এই cycle-এ late fee ইতিমধ্যে ধরা হয়ে গেছে
                    foreach ($lateFeeMap as $info) {
                        if (! empty($info['source_item_id'])) {
                            FeeInvoiceItem::where('id', $info['source_item_id'])
                                ->update(['last_fine_charged_at' => now()]);
                        }
                    }

                    // Student Fine গুলোকে "invoiced" মার্ক করে এই invoice-এর সাথে লিংক করা হচ্ছে
                    if ($pendingStudentFines->isNotEmpty()) {
                        StudentFine::whereIn('id', $pendingStudentFines->pluck('id'))
                            ->update([
                                'status' => 'invoiced',
                                'fee_invoice_id' => $invoice->id,
                            ]);
                    }

                    activity()
                        ->tap(function ($activity) use ($institution) {
                            $activity->institution_id = $institution->id;
                        })
                        ->performedOn($invoice)
                        ->log("Monthly invoice auto-generated for student #{$student->id} (Late Fee: {$lateFeeTotal}, Student Fine: {$studentFineTotal})");

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

    /**
     * এই Fee Setup-এর জন্য Late Fee (FeeFine) হিসাব করে — যদি আগের কোনো
     * Invoice Item এই Fee Setup-এর জন্য Overdue (unpaid + due_date পার) থাকে,
     * এবং সেই Fee Setup-এর জন্য একটা Active FeeFine rule থাকে।
     *
     * @return array{amount: float, source_item_id: ?int}
     */
    protected function calculateLateFee(FeeSetup $feeSetup, int $studentId, Carbon $invoiceDate): array
    {
        $noFine = ['amount' => 0.0, 'source_item_id' => null];

        $fine = FeeFine::where('fee_setup_id', $feeSetup->id)
            ->where('status', true)
            ->first();

        if (! $fine) {
            return $noFine;
        }

        // পূর্বের Overdue, Unpaid Invoice Item খুঁজে বের করা হচ্ছে
        $overdueItem = FeeInvoiceItem::where('fee_setup_id', $feeSetup->id)
            ->whereHas('invoice', function ($query) use ($studentId, $invoiceDate) {
                $query->where('student_id', $studentId)
                    ->where('due_amount', '>', 0)
                    ->where('due_date', '<', $invoiceDate->toDateString());
            })
            ->latest('id')
            ->first();

        if (! $overdueItem) {
            return $noFine;
        }

        // এই cycle-এ Late Fee (re)charge করার উপযুক্ত সময় হয়েছে কিনা চেক করা হচ্ছে
        if (! $this->isLateFineDue($overdueItem, $fine, $invoiceDate)) {
            return $noFine;
        }

        $amount = $fine->fine_type === 'percentage'
            ? round(((float) $feeSetup->amount) * ((float) $fine->fine_value) / 100, 2)
            : (float) $fine->fine_value;

        return [
            'amount' => $amount,
            'source_item_id' => $overdueItem->id,
        ];
    }

    /**
     * Late Fee Frequency অনুযায়ী চেক করে — আগে কখনো charge করা না হয়ে থাকলে,
     * অথবা frequency-এর ইন্টারভাল পার হয়ে গেলে, ফাইন আবার apply করা যাবে।
     */
    protected function isLateFineDue(FeeInvoiceItem $overdueItem, FeeFine $fine, Carbon $invoiceDate): bool
    {
        if (! $overdueItem->last_fine_charged_at) {
            return true;
        }

        $lastCharged = Carbon::parse($overdueItem->last_fine_charged_at);

        return match ($fine->late_fee_frequency) {
            'one_time' => false, // একবার charge হয়ে গেলে আর হবে না
            'daily'    => $lastCharged->diffInDays($invoiceDate) >= 1,
            'weekly'   => $lastCharged->diffInDays($invoiceDate) >= 7,
            'monthly'  => $lastCharged->diffInMonths($invoiceDate) >= 1,
            'yearly'   => $lastCharged->diffInYears($invoiceDate) >= 1,
            default    => false,
        };
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
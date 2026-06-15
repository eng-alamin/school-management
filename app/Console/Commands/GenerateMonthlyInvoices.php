<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PricingRate;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'billing:monthly-generate {--month=} {--year=}';
    protected $description = 'Generate monthly per-student invoices for all schools';

    public function handle()
    {
        $month = $this->option('month') ?? now()->month;
        $year  = $this->option('year') ?? now()->year;

        $rate = PricingRate::where('type', 'student')->where('is_active', true)->value('rate') ?? 1.00;

        $schools = School::withoutGlobalScopes()->where('status', 1)->get();

        foreach ($schools as $school) {

            // Duplicate invoice check
            $exists = Invoice::where('school_id', $school->id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                $this->warn("Invoice already exists for {$school->name} - {$month}/{$year}");
                continue;
            }

            // ── Active student count (users table, role=student, is_active=true) ──
            $activeStudentCount = User::where('school_id', $school->id)
                ->where('role', 'student')
                ->where('is_active', true)
                ->count();

            if ($activeStudentCount < 1) {
                $this->info("{$school->name}: No active students, skipped.");
                continue;
            }

            $totalAmount = $activeStudentCount * $rate;

            $invoice = Invoice::create([
                'school_id'      => $school->id,
                'invoice_no'     => 'INV-' . $school->id . '-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT),
                'month'          => $month,
                'year'           => $year,
                'total_amount'   => $totalAmount,
                'discount'       => 0,
                'payable_amount' => $totalAmount,
                'status'         => 'pending',
                'due_date'       => Carbon::create($year, $month, 1)->addMonth()->addDays(9),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'type'       => 'student',
                'quantity'   => $activeStudentCount,
                'rate'       => $rate,
                'amount'     => $totalAmount,
            ]);

            $this->info("Invoice generated for {$school->name}: {$activeStudentCount} students × ৳{$rate} = ৳{$totalAmount}");
        }

        $this->info('Monthly billing generation completed.');
    }
}
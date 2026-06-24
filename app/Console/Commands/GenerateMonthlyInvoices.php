<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PricingRate;
use App\Models\SmsLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'billing:monthly-generate {--month=} {--year=}';
    protected $description = 'Generate monthly per-student and per-SMS invoices for all institutions';

    public function handle()
    {
        $month = $this->option('month') ?? now()->month;
        $year  = $this->option('year') ?? now()->year;

        $studentRate = PricingRate::where('type', 'student')->where('is_active', true)->value('rate') ?? 1.00;
        $smsRate     = PricingRate::where('type', 'sms')->where('is_active', true)->value('rate') ?? 0;

        $institutions = Institution::withoutGlobalScopes()->where('status', 1)->get();

        foreach ($institutions as $institution) {

            // Duplicate invoice check
            $exists = Invoice::where('institution_id', $institution->id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                $this->warn("Invoice already exists for {$institution->name} - {$month}/{$year}");
                continue;
            }

            // ── Active student count (users table, role=student, is_active=true) ──
            $activeStudentCount = User::where('institution_id', $institution->id)
                ->where('role', 'student')
                ->where('is_active', true)
                ->count();

            // ── এই মাসে সফলভাবে পাঠানো SMS সংখ্যা ──
            $smsCount = SmsLog::where('institution_id', $institution->id)
                ->where('status', 'sent')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            // student বা sms কোনোটাই না থাকলে invoice বানানোর দরকার নেই
            if ($activeStudentCount < 1 && $smsCount < 1) {
                $this->info("{$institution->name}: কোনো active student বা SMS নেই, skip করা হলো।");
                continue;
            }

            $studentAmount = $activeStudentCount * $studentRate;
            $smsAmount     = $smsCount * $smsRate;
            $totalAmount   = $studentAmount + $smsAmount;

            $invoice = Invoice::create([
                'institution_id'      => $institution->id,
                'invoice_no'     => 'INV-' . $institution->id . '-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT),
                'month'          => $month,
                'year'           => $year,
                'total_amount'   => $totalAmount,
                'discount'       => 0,
                'payable_amount' => $totalAmount,
                'status'         => 'pending',
                'due_date'       => Carbon::create($year, $month, 1)->addMonth()->addDays(9),
            ]);

            if ($activeStudentCount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'type'       => 'student',
                    'quantity'   => $activeStudentCount,
                    'rate'       => $studentRate,
                    'amount'     => $studentAmount,
                ]);
            }

            if ($smsCount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'type'       => 'sms',
                    'quantity'   => $smsCount,
                    'rate'       => $smsRate,
                    'amount'     => $smsAmount,
                ]);
            }

            $this->info("Invoice generated for {$institution->name}: {$activeStudentCount} students × ৳{$studentRate} + {$smsCount} SMS × ৳{$smsRate} = ৳{$totalAmount}");
        }

        $this->info('Monthly billing generation completed.');
    }
}
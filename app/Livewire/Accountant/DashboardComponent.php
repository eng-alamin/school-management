<?php

namespace App\Livewire\Accountant;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardComponent extends Component
{
    // ── Stats (Accountant relevant only) ────────────────────────────────────
    public int   $totalStudents        = 0;

    // ── Fee / Finance ──────────────────────────────────────────────────────
    public float $totalFeeCollected    = 0;
    public float $totalFeeDue          = 0;
    public float $totalFeeToday        = 0;
    public float $totalDeposits        = 0;
    public float $totalExpenses        = 0;
    public float $accountBalance       = 0;

    // ── Salary ─────────────────────────────────────────────────────────────
    public float $salaryPaidThisMonth   = 0;
    public float $salaryUnpaidThisMonth = 0;

    // ── Notices & Mailbox ────────────────────────────────────────────────────
    public int   $activeNotices        = 0;
    public int   $unreadMessages       = 0;

    // ── Recent & Lists ─────────────────────────────────────────────────────
    public $recentInvoices;
    public $recentPayments;
    public $recentNotices;
    public $recentMessages;
    public $recentActivities;
    public $monthlyFeeChart;

    public function mount(): void
    {
        $institutionId = auth()->user()->institution_id;
        $today = Carbon::today();
        $month = Carbon::now()->format('Y-m');

        // ── Current Session ────────────────────────────────────────────────
        $this->currentSessionId = DB::table('academic_sessions')
            ->where('institution_id', $institutionId)
            ->where('is_current', true)
            ->value('id');

        // ── Total Students (Read-only view for Accountant) ──────────────────
        $this->totalStudents = DB::table('students')
            ->where('institution_id', $institutionId)
            ->where('session_id', $this->currentSessionId)
            ->count();

        // ── Notices & Messages ─────────────────────────────────────────────
        $this->activeNotices = DB::table('notices')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->where('published_at', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $today);
            })
            ->count();

        $this->unreadMessages = DB::table('messages')
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->where('is_trashed_by_receiver', false)
            ->where('is_deleted_by_receiver', false)
            ->count();

        // ── Fee Collection ─────────────────────────────────────────────────
        $feeStats = DB::table('fee_invoices')
            ->where('institution_id', $institutionId)
            ->selectRaw('
                COALESCE(SUM(paid_amount), 0) AS total_paid,
                COALESCE(SUM(due_amount), 0)  AS total_due
            ')
            ->first();

        $this->totalFeeCollected = (float) ($feeStats->total_paid ?? 0);
        $this->totalFeeDue       = (float) ($feeStats->total_due  ?? 0);

        // ✅ FIX: fee_payments এ column 'amount', 'paid_amount' না
        $this->totalFeeToday = (float) DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->whereDate('payment_date', $today)
            ->sum('amount');

        // ── Office Accounts ────────────────────────────────────────────────
        $openingBalance = (float) DB::table('office_accounts')
            ->where('institution_id', $institutionId)
            ->sum('opening_balance');

        $this->totalDeposits = (float) DB::table('office_deposits')
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $this->totalExpenses = (float) DB::table('office_expenses')
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $this->accountBalance = $openingBalance + $this->totalDeposits - $this->totalExpenses;

        // ── Salary (current month) ─────────────────────────────────────────
        $salaryStats = DB::table('salary_payments')
            ->where('institution_id', $institutionId)
            ->whereNull('deleted_at')
            ->whereRaw("DATE_FORMAT(month, '%Y-%m') = ?", [$month])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'paid'   THEN net_salary ELSE 0 END), 0) AS paid,
                COALESCE(SUM(CASE WHEN status = 'unpaid' THEN net_salary ELSE 0 END), 0) AS unpaid
            ")
            ->first();

        $this->salaryPaidThisMonth   = (float) ($salaryStats->paid   ?? 0);
        $this->salaryUnpaidThisMonth = (float) ($salaryStats->unpaid ?? 0);

        // ── Recent Invoices ────────────────────────────────────────────────
        $this->recentInvoices = DB::table('fee_invoices as fi')
            ->join('students as s', 's.id', '=', 'fi.student_id')
            ->where('fi.institution_id', $institutionId)
            ->select(
                'fi.id', 'fi.invoice_no', 's.name as student_name',
                'fi.total_amount', 'fi.paid_amount', 'fi.due_amount',
                'fi.payment_status', 'fi.invoice_date'
            )
            ->orderByDesc('fi.created_at')
            ->limit(5)
            ->get();

        // ── Recent Fee Payments ────────────────────────────────────────────
        // ✅ FIX: fp.paid_amount → fp.amount, payment_status fee_invoices থেকে join করে আনা
        $this->recentPayments = DB::table('fee_payments as fp')
            ->join('students as s', 's.id', '=', 'fp.student_id')
            ->leftJoin('fee_invoices as fi', 'fi.id', '=', 'fp.fee_invoice_id')
            ->where('fp.institution_id', $institutionId)
            ->select(
                'fp.id', 's.name as student_name', 'fp.amount',
                'fp.payment_method', 'fp.payment_date', 'fi.payment_status'
            )
            ->orderByDesc('fp.created_at')
            ->limit(5)
            ->get();

        // ── Recent Notices (Read-only) ──────────────────────────────────────
        $this->recentNotices = DB::table('notices')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->select('id', 'title', 'audience', 'priority', 'published_at')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        // ── Recent Messages (Mailbox) ───────────────────────────────────────
        $this->recentMessages = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.receiver_id', auth()->id())
            ->where('m.is_deleted_by_receiver', false)
            ->select('m.id', 'u.name as sender_name', 'm.subject', 'm.is_read', 'm.created_at')
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get();

        // ── Recent Activities (Fee/Salary/Office related only) ──────────────
        $this->recentActivities = DB::table('activity_log')
            ->where('institution_id', $institutionId)
            ->whereIn('properties->type', ['payment', 'invoice', 'salary', 'deposit', 'expense'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'description', 'properties', 'created_at')
            ->get()
            ->map(function ($act) {
                $props = json_decode($act->properties, true);
                $act->icon = $props['icon'] ?? 'notifications';
                return $act;
            });

        // ── Monthly Fee Collection (last 6 months) for chart ───────────────
        // ✅ FIX: SUM(paid_amount) → SUM(amount)
        $this->monthlyFeeChart = DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->where('payment_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, COALESCE(SUM(amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'month' => Carbon::createFromFormat('Y-m', $row->month)->format('M Y'),
                'total' => (float) $row->total,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.accountant.dashboard-component')
            ->layout('layouts.accountant.app', [
                'title' => 'Dashboard | ' . institution()->name,
            ]);
    }
}
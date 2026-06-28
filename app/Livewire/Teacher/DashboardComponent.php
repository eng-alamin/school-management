<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardComponent extends Component
{
    // ── Stats ──────────────────────────────────────────────────────────────
    public int   $totalStudents        = 0;
    public int   $totalEmployees       = 0;
    public int   $totalClasses         = 0;
    public int   $activeNotices        = 0;
    public int   $unreadMessages       = 0;

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

    // ── Attendance (today) ─────────────────────────────────────────────────
    public int   $studentsPresentToday  = 0;
    public int   $studentsAbsentToday   = 0;
    public int   $employeesPresentToday = 0;

    // ── New Admissions (this month) ────────────────────────────────────────
    public int   $newAdmissionsThisMonth = 0;

    // ── Pending Homework ───────────────────────────────────────────────────
    public int   $pendingHomework        = 0;

    // ── Upcoming Exams ─────────────────────────────────────────────────────
    public int   $upcomingExams          = 0;

    // ── Attendance % ───────────────────────────────────────────────────────
    public float $attendancePercent      = 0;

    // ── Inventory ──────────────────────────────────────────────────────────
    public float $inventorySalesToday    = 0;

    // ── Recent & Lists ─────────────────────────────────────────────────────
    public $recentInvoices;
    public $recentPayments;
    public $recentNotices;
    public $recentMessages;
    public $recentActivities;
    public $todayBirthdays;
    public $monthlyFeeChart;

    // ── Filters ────────────────────────────────────────────────────────────
    public ?int $currentSessionId = null;

    public function mount(): void
    {
        $schoolId = auth()->user()->institution_id;
        $today    = Carbon::today();
        $month    = Carbon::now()->format('Y-m');

        // ── Current Session ────────────────────────────────────────────────
        $this->currentSessionId = DB::table('academic_sessions')
            ->where('institution_id', $schoolId)
            ->where('is_current', true)
            ->value('id');

        // ── Students & Employees ───────────────────────────────────────────
        $this->totalStudents = DB::table('students')
            ->where('institution_id', $schoolId)
            ->count();

        $this->totalEmployees = DB::table('employees')
            ->where('institution_id', $schoolId)
            ->count();

        $this->totalClasses = DB::table('academic_classes')
            ->where('institution_id', $schoolId)
            ->count();

        // ── New Admissions this month ──────────────────────────────────────
        $this->newAdmissionsThisMonth = DB::table('students')
            ->where('institution_id', $schoolId)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
            ->count();

        // ── Pending Homework ───────────────────────────────────────────────
        // $this->pendingHomework = DB::table('homeworks')
        //     ->where('institution_id', $schoolId)
        //     ->where('status', 'published')      
        //     ->whereDate('submission_date', '>=', $today)
        //     ->count();
            // ->where('institution_id', $schoolId)
            // ->where('status', 'pending')
            // ->whereDate('due_date', '>=', $today)
            // ->count();

        // ── Upcoming Exams ─────────────────────────────────────────────────
        // $this->upcomingExams = DB::table('exams')
        //     ->where('institution_id', $schoolId)
        //     ->whereDate('start_date', '>=', $today)
        //     ->count();

        // ── Attendance % (today) ───────────────────────────────────────────
        $totalMarked = DB::table('attendances')
            ->where('institution_id', $schoolId)
            ->where('type', 'student')
            ->whereDate('date', $today)
            ->count();

        $this->studentsPresentToday = DB::table('attendances')
            ->where('institution_id', $schoolId)
            ->where('type', 'student')
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        $this->studentsAbsentToday = DB::table('attendances')
            ->where('institution_id', $schoolId)
            ->where('type', 'student')
            ->whereDate('date', $today)
            ->where('status', 'absent')
            ->count();

        $this->attendancePercent = $totalMarked > 0
            ? round(($this->studentsPresentToday / $totalMarked) * 100, 1)
            : 0;

        $this->employeesPresentToday = DB::table('attendances')
            ->where('institution_id', $schoolId)
            ->where('type', 'employee')
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        // ── Notices & Messages ─────────────────────────────────────────────
        $this->activeNotices = DB::table('notices')
            ->where('institution_id', $schoolId)
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
            ->where('institution_id', $schoolId)
            ->selectRaw('
                COALESCE(SUM(paid_amount), 0) AS total_paid,
                COALESCE(SUM(due_amount), 0)  AS total_due
            ')
            ->first();

        $this->totalFeeCollected = (float) ($feeStats->total_paid ?? 0);
        $this->totalFeeDue       = (float) ($feeStats->total_due  ?? 0);

        $this->totalFeeToday = (float) DB::table('fee_payments')
            ->where('institution_id', $schoolId)
            ->whereDate('payment_date', $today)
            ->sum('paid_amount');

        // ── Office Accounts ────────────────────────────────────────────────
        $openingBalance       = (float) DB::table('office_accounts')
            ->where('institution_id', $schoolId)
            ->sum('opening_balance');

        $this->totalDeposits  = (float) DB::table('office_deposits')
            ->where('institution_id', $schoolId)
            ->sum('amount');

        $this->totalExpenses  = (float) DB::table('office_expenses')
            ->where('institution_id', $schoolId)
            ->sum('amount');

        $this->accountBalance = $openingBalance + $this->totalDeposits - $this->totalExpenses;

        // ── Salary (current month) ─────────────────────────────────────────
        $salaryStats = DB::table('salary_payments')
            ->where('institution_id', $schoolId)
            ->whereNull('deleted_at')
            ->whereRaw("DATE_FORMAT(month, '%Y-%m') = ?", [$month])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'paid'   THEN net_salary ELSE 0 END), 0) AS paid,
                COALESCE(SUM(CASE WHEN status = 'unpaid' THEN net_salary ELSE 0 END), 0) AS unpaid
            ")
            ->first();

        $this->salaryPaidThisMonth   = (float) ($salaryStats->paid   ?? 0);
        $this->salaryUnpaidThisMonth = (float) ($salaryStats->unpaid ?? 0);

        // ── Inventory Sales Today ──────────────────────────────────────────
        $this->inventorySalesToday = (float) DB::table('inventory_sales')
            ->where('institution_id', $schoolId)
            ->whereDate('date', $today)
            ->sum('net_payable');

        // ── Recent Invoices ────────────────────────────────────────────────
        $this->recentInvoices = DB::table('fee_invoices as fi')
            ->join('students as s', 's.id', '=', 'fi.student_id')
            ->where('fi.institution_id', $schoolId)
            ->select(
                'fi.id', 'fi.invoice_no', 's.name as student_name',
                'fi.total_amount', 'fi.paid_amount', 'fi.due_amount',
                'fi.payment_status', 'fi.invoice_date'
            )
            ->orderByDesc('fi.created_at')
            ->limit(5)
            ->get();

        // ── Recent Fee Payments ────────────────────────────────────────────
        $this->recentPayments = DB::table('fee_payments as fp')
            ->join('students as s', 's.id', '=', 'fp.student_id')
            ->where('fp.institution_id', $schoolId)
            ->select(
                'fp.id', 's.name as student_name', 'fp.paid_amount',
                'fp.payment_method', 'fp.payment_date', 'fp.payment_status'
            )
            ->orderByDesc('fp.created_at')
            ->limit(5)
            ->get();

        // ── Recent Notices ─────────────────────────────────────────────────
        $this->recentNotices = DB::table('notices')
            ->where('institution_id', $schoolId)
            ->where('status', 'active')
            ->select('id', 'title', 'audience', 'priority', 'published_at')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        // ── Recent Messages ────────────────────────────────────────────────
        $this->recentMessages = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.receiver_id', auth()->id())
            ->where('m.is_deleted_by_receiver', false)
            ->select('m.id', 'u.name as sender_name', 'm.subject', 'm.is_read', 'm.created_at')
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get();

        // ── Recent Activities ──────────────────────────────────────────────
        // Assumes an `activity_logs` table with: institution_id, description, icon, created_at
        $this->recentActivities = DB::table('activity_log')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'description', 'properties', 'created_at')
            ->get()
            ->map(function ($act) {
                $props = json_decode($act->properties, true);
                $act->icon = $props['icon'] ?? 'notifications';
                return $act;
            });

        // ── Today's Birthdays ──────────────────────────────────────────────
        $todayMD = $today->format('m-d');

        $studentBirthdays = DB::table('students')
            ->where('institution_id', $schoolId)
            ->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [$todayMD])
            ->select(
                'name',
                DB::raw("'Student' as role")
            )
            ->get();

        $employeeBirthdays = DB::table('employees as e')
            ->leftJoin('employee_designations as d', 'd.id', '=', 'e.designation_id')
            ->where('e.institution_id', $schoolId)
            ->whereRaw("DATE_FORMAT(e.dob, '%m-%d') = ?", [$todayMD])
            ->select(
                'e.name',
                DB::raw("COALESCE(d.name, 'Staff') as role")
            )
            ->get();

        $this->todayBirthdays = $studentBirthdays->merge($employeeBirthdays)->take(5);

        // ── Monthly Fee Collection (last 6 months) for chart ───────────────
        $this->monthlyFeeChart = DB::table('fee_payments')
            ->where('institution_id', $schoolId)
            ->where('payment_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, COALESCE(SUM(paid_amount), 0) as total")
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
        return view('livewire.teacher.dashboard-component')
            ->layout('layouts.teacher.app', [
                'title' => 'Dashboard | Monarchy School',
            ]);
    }
}
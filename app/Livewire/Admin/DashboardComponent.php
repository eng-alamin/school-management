<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardComponent extends Component
{
    // ── Stats ──────────────────────────────────────────────────────────────
    public int   $totalStudents        = 0;
    public int   $totalTeachers        = 0;
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

    // ── Exams ─────────────────────────────────────────────────────
    public int   $upcomingExams          = 0;
    public int   $todayExams             = 0;
    public int   $completedExams         = 0;

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

    // ── Dynamic Trend (% Change) — প্রতিটার সাথে ['percent'=>, 'direction'=>'up'/'down'] থাকবে ──
    public array $trendStudents    = ['percent' => 0, 'direction' => 'up'];
    public array $trendStaffs      = ['percent' => 0, 'direction' => 'up'];
    public array $trendTeachers    = ['percent' => 0, 'direction' => 'up'];
    public array $trendAttendance  = ['percent' => 0, 'direction' => 'up'];
    public array $trendAdmissions  = ['percent' => 0, 'direction' => 'up'];
    public array $trendHomework    = ['percent' => 0, 'direction' => 'up'];
    public array $trendFeeCollected = ['percent' => 0, 'direction' => 'up'];
    public array $trendFeeToday = ['percent' => 0, 'direction' => 'up'];
    public array $trendFeeDue      = ['percent' => 0, 'direction' => 'up'];

    public function mount(): void
    {
        $institutionId = auth()->user()->institution_id;
        $today    = Carbon::today();
        $yesterday = Carbon::yesterday();
        $month    = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonthNoOverflow()->format('Y-m');

        // ── Current Session ────────────────────────────────────────────────
        $this->currentSessionId = DB::table('academic_sessions')
            ->where('institution_id', $institutionId)
            ->where('is_current', true)
            ->value('id');

        // ── Students & Employees ───────────────────────────────────────────
        $this->totalStudents = DB::table('students')
            ->where('institution_id', $institutionId)
            ->where('session_id', $this->currentSessionId)
            ->count();

        $this->totalEmployees = DB::table('employees')
            ->where('institution_id', $institutionId)
            ->count();

        $this->totalTeachers = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.institution_id', $institutionId)
            ->where('users.role', 'teacher')
            ->count();

        $this->totalClasses = DB::table('academic_classes')
            ->where('institution_id', $institutionId)
            ->count();

        // ── New Admissions this month / last month ─────────────────────────
        $this->newAdmissionsThisMonth = DB::table('students')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
            ->count();

        $admissionsLastMonth = DB::table('students')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$lastMonth])
            ->count();

        $this->trendAdmissions = $this->percentChange($this->newAdmissionsThisMonth, $admissionsLastMonth);

        // ── Total Students Growth (এই মাসের Admission / আগের Total) ─────────
        $studentsBeforeThisMonth = max(0, $this->totalStudents - $this->newAdmissionsThisMonth);
        $this->trendStudents = $this->percentChange($this->totalStudents, $studentsBeforeThisMonth);

        // ── Total Staffs Growth ─────────────────────────────────────────────
        $employeesThisMonth = DB::table('employees')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
            ->count();
        $employeesBeforeThisMonth = max(0, $this->totalEmployees - $employeesThisMonth);
        $this->trendStaffs = $this->percentChange($this->totalEmployees, $employeesBeforeThisMonth);

        // ── Total Teachers Growth ────────────────────────────────────────────
        $teachersThisMonth = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.institution_id', $institutionId)
            ->where('users.role', 'teacher')
            ->whereRaw("DATE_FORMAT(employees.created_at, '%Y-%m') = ?", [$month])
            ->count();
        $teachersBeforeThisMonth = max(0, $this->totalTeachers - $teachersThisMonth);
        $this->trendTeachers = $this->percentChange($this->totalTeachers, $teachersBeforeThisMonth);

        // ── Pending Homework ───────────────────────────────────────────────
        $this->pendingHomework = DB::table('homeworks')
            ->where('institution_id', $institutionId)
            ->where('status', 'published')
            ->whereDate('submission_date', '>=', $today)
            ->count();

        // Homework Trend — এই সপ্তাহে তৈরি vs গত সপ্তাহে তৈরি Homework সংখ্যা
        $homeworkThisWeek = DB::table('homeworks')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $homeworkLastWeek = DB::table('homeworks')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();
        $this->trendHomework = $this->percentChange($homeworkThisWeek, $homeworkLastWeek);

        // Upcoming Exams
        $this->upcomingExams = DB::table('exam_schedules')
            ->where('institution_id', $institutionId)
            ->where('is_published', true)
            ->whereDate('exam_date', '>=', today())
            ->count();

        // Today's Exams
        $this->todayExams = DB::table('exam_schedules')
            ->where('institution_id', $institutionId)
            ->where('is_published', true)
            ->whereDate('exam_date', today())
            ->count();

        // Completed Exams
        $this->completedExams = DB::table('exam_schedules')
            ->where('institution_id', $institutionId)
            ->where('is_published', true)
            ->whereDate('exam_date', '<', today())
            ->count();

        // ── Attendance % (today vs yesterday) ───────────────────────────────
        $totalMarked = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'student')
            ->whereDate('date', $today)
            ->count();

        $this->studentsPresentToday = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'student')
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        $this->studentsAbsentToday = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'student')
            ->whereDate('date', $today)
            ->where('status', 'absent')
            ->count();

        $this->attendancePercent = $totalMarked > 0
            ? round(($this->studentsPresentToday / $totalMarked) * 100, 1)
            : 0;

        // গতকালের Attendance %
        $totalMarkedYesterday = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'student')
            ->whereDate('date', $yesterday)
            ->count();

        $presentYesterday = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'student')
            ->whereDate('date', $yesterday)
            ->where('status', 'present')
            ->count();

        $attendancePercentYesterday = $totalMarkedYesterday > 0
            ? round(($presentYesterday / $totalMarkedYesterday) * 100, 1)
            : 0;

        // Attendance-এর জন্য Point Difference (আজ - গতকাল), % Growth না
        $attendanceDiff = round($this->attendancePercent - $attendancePercentYesterday, 1);
        $this->trendAttendance = [
            'percent'   => abs($attendanceDiff),
            'direction' => $attendanceDiff >= 0 ? 'up' : 'down',
        ];

        $this->employeesPresentToday = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'employee')
            ->whereDate('date', $today)
            ->where('status', 'present')
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

        $this->totalFeeToday = (float) DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->whereDate('payment_date', $today)
            ->sum('amount');

        // Fee Collected Trend — এই মাস vs গত মাস (fee_payments থেকে)
        $feeCollectedThisMonth = (float) DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(payment_date, '%Y-%m') = ?", [$month])
            ->sum('amount');

        $feeCollectedLastMonth = (float) DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(payment_date, '%Y-%m') = ?", [$lastMonth])
            ->sum('amount');

        $this->trendFeeCollected = $this->percentChange($feeCollectedThisMonth, $feeCollectedLastMonth);

        // Fee Collected Trend — এই Day vs গত Day (fee_payments থেকে)
        $feeCollectedThisDay = (float) DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $feeCollectedLastDay = (float) DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->whereDate('payment_date', $yesterday)
            ->sum('amount');

        $this->trendFeeToday = $this->percentChange($feeCollectedThisDay, $feeCollectedLastDay);

        $feeCollectedLastMonth = (float) DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(payment_date, '%Y-%m') = ?", [$lastMonth])
            ->sum('amount');

        $this->trendFeeCollected = $this->percentChange($feeCollectedThisMonth, $feeCollectedLastMonth);

        // Due Fees Trend — এই মাসের Invoice-এ তৈরি হওয়া Due vs গত মাসের
        $feeDueThisMonth = (float) DB::table('fee_invoices')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$month])
            ->sum('due_amount');

        $feeDueLastMonth = (float) DB::table('fee_invoices')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$lastMonth])
            ->sum('due_amount');

        $this->trendFeeDue = $this->percentChange($feeDueThisMonth, $feeDueLastMonth);

        // ── Office Accounts ────────────────────────────────────────────────
        $openingBalance       = (float) DB::table('office_accounts')
            ->where('institution_id', $institutionId)
            ->sum('opening_balance');

        $this->totalDeposits  = (float) DB::table('office_deposits')
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $this->totalExpenses  = (float) DB::table('office_expenses')
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

        // ── Inventory Sales Today ──────────────────────────────────────────
        $this->inventorySalesToday = (float) DB::table('inventory_sales')
            ->where('institution_id', $institutionId)
            ->whereDate('date', $today)
            ->sum('net_payable');

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

        // ── Recent Notices ─────────────────────────────────────────────────
        $this->recentNotices = DB::table('notices')
            ->where('institution_id', $institutionId)
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
        $this->recentActivities = DB::table('activity_log')
            ->where('institution_id', $institutionId)
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
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [$todayMD])
            ->select(
                'name',
                DB::raw("'Student' as role")
            )
            ->get();

        $employeeBirthdays = DB::table('employees as e')
            ->leftJoin('employee_designations as d', 'd.id', '=', 'e.designation_id')
            ->where('e.institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(e.dob, '%m-%d') = ?", [$todayMD])
            ->select(
                'e.name',
                DB::raw("COALESCE(d.name, 'Staff') as role")
            )
            ->get();

        $this->todayBirthdays = $studentBirthdays->merge($employeeBirthdays)->take(5);

        // ── Monthly Fee Collection (last 6 months) for chart ───────────────
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

    /**
     * দুইটা সংখ্যা থেকে % Change আর Direction (up/down) বের করে।
     * $previous = 0 হলে current > 0 হলে 100% up ধরা হয়, দুইটাই 0 হলে 0%।
     */
    private function percentChange(float $current, float $previous): array
    {
        if ($previous == 0) {
            $percent = $current > 0 ? 100 : 0;
        } else {
            $percent = (($current - $previous) / $previous) * 100;
        }

        return [
            'percent'   => round(abs($percent), 1),
            'direction' => $percent >= 0 ? 'up' : 'down',
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard-component')
            ->layout('layouts.admin.app', [
                'title' => 'Dashboard | ' . institution()->name,
            ]);
    }
}
<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Notice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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

    // ── Notice View Modal ─────────────────────────────────────────────────
    public bool $showViewNoticeModal = false;
    public ?Notice $viewRecord = null;

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
        $this->loadDashboardData();
    }

    /**
     * Dashboard এর সব stat/list ডাটা cache থেকে load করে properties এ assign করে।
     *
     * IMPORTANT: এই মেথড mount() ছাড়াও render() থেকেও call হয়। কারণ এখানকার
     * অনেক property (recentInvoices, recentPayments, recentActivities ইত্যাদি)
     * raw DB::table()->get() থেকে আসা stdClass-এর Collection — Livewire এই ধরনের
     * plain stdClass object সঠিকভাবে serialize/hydrate করতে পারে না (শুধু Eloquent
     * Model বা array support করে)। ফলে mount()-এ একবার set করলে, পরবর্তী যেকোনো
     * Livewire action (যেমন openView) এর পর hydration এ এই properties খালি হয়ে
     * "background data reset" হয়ে যেত। প্রতি render() এ cache থেকে re-assign করায়
     * ডাটা সবসময় সঠিক থাকে, আর Cache::remember() এর কারণে extra DB hit ও হয় না।
     */
    private function loadDashboardData(): void
    {
        $institutionId = auth()->user()->institution_id;

        $data = Cache::remember(
            "admin_dashboard:{$institutionId}",
            now()->addMinutes(5),
            fn () => $this->buildDashboardData($institutionId)
        );

        foreach ($data as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * Recent Notices list-এ click করলে View Modal ওপেন করে।
     * institution_id scope defense-in-depth হিসেবে explicit রাখা হলো,
     * যদিও Notice model-এ global scope আছে।
     */
    public function openViewNotice(int $id): void
    {
        $this->viewRecord = Notice::with('creator')
            ->where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->showViewNoticeModal = true;
    }

    public function closeViewNoticeModal(): void
    {
        $this->showViewNoticeModal = false;
        $this->viewRecord    = null;
    }

    /**
     * সব ড্যাশবোর্ড ডাটা fresh calculate করে array আকারে রিটার্ন করে।
     * এই মেথডের রেজাল্টই Cache::remember() এ ৫ মিনিটের জন্য cache হয়।
     */
    private function buildDashboardData(int $institutionId): array
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        $result = [];

        // ── Current Session ────────────────────────────────────────────────
        $result['currentSessionId'] = DB::table('academic_sessions')
            ->where('institution_id', $institutionId)
            ->where('is_current', true)
            ->value('id');

        // ── Students & Employees ───────────────────────────────────────────
        $result['totalStudents'] = DB::table('students')
            ->where('institution_id', $institutionId)
            ->where('session_id', $result['currentSessionId'])
            ->count();

        $result['totalEmployees'] = DB::table('employees')
            ->where('institution_id', $institutionId)
            ->count();

        $result['totalTeachers'] = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.institution_id', $institutionId)
            ->where('users.role', 'teacher')
            ->count();

        $result['totalClasses'] = DB::table('academic_classes')
            ->where('institution_id', $institutionId)
            ->count();

        // ── New Admissions this month / last month ─────────────────────────
        $result['newAdmissionsThisMonth'] = DB::table('students')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $admissionsLastMonth = DB::table('students')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $result['trendAdmissions'] = $this->percentChange($result['newAdmissionsThisMonth'], $admissionsLastMonth);

        // ── Total Students Growth (এই মাসের Admission / আগের Total) ─────────
        $studentsBeforeThisMonth = max(0, $result['totalStudents'] - $result['newAdmissionsThisMonth']);
        $result['trendStudents'] = $this->percentChange($result['totalStudents'], $studentsBeforeThisMonth);

        // ── Total Staffs Growth ─────────────────────────────────────────────
        $employeesThisMonth = DB::table('employees')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();
        $employeesBeforeThisMonth = max(0, $result['totalEmployees'] - $employeesThisMonth);
        $result['trendStaffs'] = $this->percentChange($result['totalEmployees'], $employeesBeforeThisMonth);

        // ── Total Teachers Growth ────────────────────────────────────────────
        $teachersThisMonth = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.institution_id', $institutionId)
            ->where('users.role', 'teacher')
            ->whereBetween('employees.created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();
        $teachersBeforeThisMonth = max(0, $result['totalTeachers'] - $teachersThisMonth);
        $result['trendTeachers'] = $this->percentChange($result['totalTeachers'], $teachersBeforeThisMonth);

        // ── Pending Homework ───────────────────────────────────────────────
        $result['pendingHomework'] = DB::table('homeworks')
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
        $result['trendHomework'] = $this->percentChange($homeworkThisWeek, $homeworkLastWeek);

        $examStats = DB::table('exam_schedules')
            ->where('institution_id', $institutionId)
            ->where('is_published', true)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN exam_date >= ? THEN 1 ELSE 0 END), 0) AS upcoming,
                COALESCE(SUM(CASE WHEN exam_date = ? THEN 1 ELSE 0 END), 0) AS today,
                COALESCE(SUM(CASE WHEN exam_date < ? THEN 1 ELSE 0 END), 0) AS completed
            ", [$today, $today, $today])
            ->first();

        $result['upcomingExams']  = (int) ($examStats->upcoming  ?? 0);
        $result['todayExams']     = (int) ($examStats->today     ?? 0);
        $result['completedExams'] = (int) ($examStats->completed ?? 0);

        $studentAttendanceStats = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'student')
            ->whereDate('date', $today)
            ->selectRaw("
                COUNT(*) AS total_marked,
                COALESCE(SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END), 0) AS present,
                COALESCE(SUM(CASE WHEN status = 'absent'  THEN 1 ELSE 0 END), 0) AS absent
            ")
            ->first();

        $totalMarked = (int) ($studentAttendanceStats->total_marked ?? 0);
        $result['studentsPresentToday'] = (int) ($studentAttendanceStats->present ?? 0);
        $result['studentsAbsentToday']  = (int) ($studentAttendanceStats->absent  ?? 0);

        $result['attendancePercent'] = $totalMarked > 0
            ? round(($result['studentsPresentToday'] / $totalMarked) * 100, 1)
            : 0;

        // গতকালের Attendance %
        $yesterdayAttendanceStats = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'student')
            ->whereDate('date', $yesterday)
            ->selectRaw("
                COUNT(*) AS total_marked,
                COALESCE(SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END), 0) AS present
            ")
            ->first();

        $totalMarkedYesterday = (int) ($yesterdayAttendanceStats->total_marked ?? 0);
        $presentYesterday     = (int) ($yesterdayAttendanceStats->present ?? 0);

        $attendancePercentYesterday = $totalMarkedYesterday > 0
            ? round(($presentYesterday / $totalMarkedYesterday) * 100, 1)
            : 0;

        // Attendance-এর জন্য Point Difference (আজ - গতকাল), % Growth না
        $attendanceDiff = round($result['attendancePercent'] - $attendancePercentYesterday, 1);
        $result['trendAttendance'] = [
            'percent'   => abs($attendanceDiff),
            'direction' => $attendanceDiff >= 0 ? 'up' : 'down',
        ];

        $result['employeesPresentToday'] = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'employee')
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        // ── Notices & Messages ─────────────────────────────────────────────
        $result['activeNotices'] = DB::table('notices')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->where('published_at', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $today);
            })
            ->count();

        $result['unreadMessages'] = DB::table('messages')
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

        $result['totalFeeCollected'] = (float) ($feeStats->total_paid ?? 0);
        $result['totalFeeDue']       = (float) ($feeStats->total_due  ?? 0);

        $feePaymentStats = DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN DATE(payment_date) = ? THEN amount ELSE 0 END), 0) AS today,
                COALESCE(SUM(CASE WHEN DATE(payment_date) = ? THEN amount ELSE 0 END), 0) AS yesterday,
                COALESCE(SUM(CASE WHEN payment_date BETWEEN ? AND ? THEN amount ELSE 0 END), 0) AS this_month,
                COALESCE(SUM(CASE WHEN payment_date BETWEEN ? AND ? THEN amount ELSE 0 END), 0) AS last_month
            ", [$today, $yesterday, $thisMonthStart, $thisMonthEnd, $lastMonthStart, $lastMonthEnd])
            ->first();

        $result['totalFeeToday'] = (float) ($feePaymentStats->today ?? 0);

        $feeCollectedThisDay  = (float) ($feePaymentStats->today ?? 0);
        $feeCollectedLastDay  = (float) ($feePaymentStats->yesterday ?? 0);
        $result['trendFeeToday'] = $this->percentChange($feeCollectedThisDay, $feeCollectedLastDay);

        $feeCollectedThisMonth = (float) ($feePaymentStats->this_month ?? 0);
        $feeCollectedLastMonth = (float) ($feePaymentStats->last_month ?? 0);
        $result['trendFeeCollected'] = $this->percentChange($feeCollectedThisMonth, $feeCollectedLastMonth);

        // Due Fees Trend — এই মাসের Invoice-এ তৈরি হওয়া Due vs গত মাসের
        $feeDueStats = DB::table('fee_invoices')
            ->where('institution_id', $institutionId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN invoice_date BETWEEN ? AND ? THEN due_amount ELSE 0 END), 0) AS this_month,
                COALESCE(SUM(CASE WHEN invoice_date BETWEEN ? AND ? THEN due_amount ELSE 0 END), 0) AS last_month
            ", [$thisMonthStart, $thisMonthEnd, $lastMonthStart, $lastMonthEnd])
            ->first();

        $feeDueThisMonth = (float) ($feeDueStats->this_month ?? 0);
        $feeDueLastMonth = (float) ($feeDueStats->last_month ?? 0);
        $result['trendFeeDue'] = $this->percentChange($feeDueThisMonth, $feeDueLastMonth);

        // ── Office Accounts ────────────────────────────────────────────────
        $openingBalance = (float) DB::table('office_accounts')
            ->where('institution_id', $institutionId)
            ->sum('opening_balance');

        $result['totalDeposits'] = (float) DB::table('office_deposits')
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $result['totalExpenses'] = (float) DB::table('office_expenses')
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $result['accountBalance'] = $openingBalance + $result['totalDeposits'] - $result['totalExpenses'];

        // ── Salary (current month) ─────────────────────────────────────────
        $salaryStats = DB::table('salary_payments')
            ->where('institution_id', $institutionId)
            ->whereNull('deleted_at')
            ->whereBetween('month', [$thisMonthStart, $thisMonthEnd])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'paid'   THEN net_salary ELSE 0 END), 0) AS paid,
                COALESCE(SUM(CASE WHEN status = 'unpaid' THEN net_salary ELSE 0 END), 0) AS unpaid
            ")
            ->first();

        $result['salaryPaidThisMonth']   = (float) ($salaryStats->paid   ?? 0);
        $result['salaryUnpaidThisMonth'] = (float) ($salaryStats->unpaid ?? 0);

        // ── Inventory Sales Today ──────────────────────────────────────────
        $result['inventorySalesToday'] = (float) DB::table('inventory_sales')
            ->where('institution_id', $institutionId)
            ->whereDate('date', $today)
            ->sum('net_payable');

        // ── Recent Invoices ────────────────────────────────────────────────
        $result['recentInvoices'] = DB::table('fee_invoices as fi')
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
        $result['recentPayments'] = DB::table('fee_payments as fp')
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
        $result['recentNotices'] = DB::table('notices')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->select('id', 'title', 'audience', 'priority', 'published_at')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        // ── Recent Messages ────────────────────────────────────────────────
        $result['recentMessages'] = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.receiver_id', auth()->id())
            ->where('m.is_deleted_by_receiver', false)
            ->select('m.id', 'u.name as sender_name', 'm.subject', 'm.is_read', 'm.created_at')
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get();

        // ── Recent Activities ──────────────────────────────────────────────
        $result['recentActivities'] = DB::table('activity_log')
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

        $result['todayBirthdays'] = $studentBirthdays->merge($employeeBirthdays)->take(5);

        // ── Monthly Fee Collection (last 6 months) for chart ───────────────
        $result['monthlyFeeChart'] = DB::table('fee_payments')
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

        return $result;
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
        $this->loadDashboardData();

        $institution = Auth::user()->institution;

        return view('livewire.admin.dashboard-component', [
                'progressPercent' => $institution->setupProgressPercent(),
            ])
            ->layout('layouts.admin.app', [
                'title' => 'Dashboard | ' . institution()->name,
            ]);
    }
}
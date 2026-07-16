<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Full Admin Dashboard stats (Students, Fee, Attendance, Salary, Exams, etc.)
     * Route: GET /api/admin/dashboard
     *
     * Ager Livewire\Admin\DashboardComponent-er buildDashboardData() logic
     * ekhane hubohu rakha hoyeche, sudhu view render korar bodole JSON return kore.
     */
    public function stats(): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $data = Cache::remember(
            "admin_dashboard:{$institutionId}",
            now()->addMinutes(5),
            fn () => $this->buildDashboardData($institutionId)
        );

        return response()->json($data);
    }

    /**
     * Sob dashboard data fresh calculate kore array hisebe return kore.
     * Ei method-er result-i Cache::remember() e 5 minute-er jonno cache hoy.
     */
    private function buildDashboardData(int $institutionId): array
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        // ── Current Session ────────────────────────────────────────────────
        $currentSessionId = DB::table('academic_sessions')
            ->where('institution_id', $institutionId)
            ->where('is_current', true)
            ->value('id');

        // ── Students & Employees ───────────────────────────────────────────
        $totalStudents = DB::table('students')
            ->where('institution_id', $institutionId)
            ->where('session_id', $currentSessionId)
            ->count();

        $totalEmployees = DB::table('employees')
            ->where('institution_id', $institutionId)
            ->count();

        $totalTeachers = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.institution_id', $institutionId)
            ->where('users.role', 'teacher')
            ->count();

        $totalClasses = DB::table('academic_classes')
            ->where('institution_id', $institutionId)
            ->count();

        // ── New Admissions this month / last month ─────────────────────────
        $newAdmissionsThisMonth = DB::table('students')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $admissionsLastMonth = DB::table('students')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $trendAdmissions = $this->percentChange($newAdmissionsThisMonth, $admissionsLastMonth);

        // ── Total Students Growth ────────────────────────────────────────────
        $studentsBeforeThisMonth = max(0, $totalStudents - $newAdmissionsThisMonth);
        $trendStudents = $this->percentChange($totalStudents, $studentsBeforeThisMonth);

        // ── Total Staffs Growth ─────────────────────────────────────────────
        $employeesThisMonth = DB::table('employees')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();
        $employeesBeforeThisMonth = max(0, $totalEmployees - $employeesThisMonth);
        $trendStaffs = $this->percentChange($totalEmployees, $employeesBeforeThisMonth);

        // ── Total Teachers Growth ────────────────────────────────────────────
        $teachersThisMonth = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.institution_id', $institutionId)
            ->where('users.role', 'teacher')
            ->whereBetween('employees.created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();
        $teachersBeforeThisMonth = max(0, $totalTeachers - $teachersThisMonth);
        $trendTeachers = $this->percentChange($totalTeachers, $teachersBeforeThisMonth);

        // ── Pending Homework ───────────────────────────────────────────────
        $pendingHomework = DB::table('homeworks')
            ->where('institution_id', $institutionId)
            ->where('status', 'published')
            ->whereDate('submission_date', '>=', $today)
            ->count();

        $homeworkThisWeek = DB::table('homeworks')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $homeworkLastWeek = DB::table('homeworks')
            ->where('institution_id', $institutionId)
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();
        $trendHomework = $this->percentChange($homeworkThisWeek, $homeworkLastWeek);

        // ── Exams ────────────────────────────────────────────────────────────
        $examStats = DB::table('exam_schedules')
            ->where('institution_id', $institutionId)
            ->where('is_published', true)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN exam_date >= ? THEN 1 ELSE 0 END), 0) AS upcoming,
                COALESCE(SUM(CASE WHEN exam_date = ? THEN 1 ELSE 0 END), 0) AS today,
                COALESCE(SUM(CASE WHEN exam_date < ? THEN 1 ELSE 0 END), 0) AS completed
            ", [$today, $today, $today])
            ->first();

        $upcomingExams  = (int) ($examStats->upcoming  ?? 0);
        $todayExams     = (int) ($examStats->today     ?? 0);
        $completedExams = (int) ($examStats->completed ?? 0);

        // ── Attendance (today) ──────────────────────────────────────────────
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

        $totalMarked          = (int) ($studentAttendanceStats->total_marked ?? 0);
        $studentsPresentToday = (int) ($studentAttendanceStats->present ?? 0);
        $studentsAbsentToday  = (int) ($studentAttendanceStats->absent  ?? 0);

        $attendancePercent = $totalMarked > 0
            ? round(($studentsPresentToday / $totalMarked) * 100, 1)
            : 0;

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

        $attendanceDiff  = round($attendancePercent - $attendancePercentYesterday, 1);
        $trendAttendance = [
            'percent'   => abs($attendanceDiff),
            'direction' => $attendanceDiff >= 0 ? 'up' : 'down',
        ];

        $employeesPresentToday = DB::table('attendances')
            ->where('institution_id', $institutionId)
            ->where('type', 'employee')
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        // ── Notices & Messages ─────────────────────────────────────────────
        $activeNotices = DB::table('notices')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->where('published_at', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $today);
            })
            ->count();

        $unreadMessages = DB::table('messages')
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

        $totalFeeCollected = (float) ($feeStats->total_paid ?? 0);
        $totalFeeDue       = (float) ($feeStats->total_due  ?? 0);

        $feePaymentStats = DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN DATE(payment_date) = ? THEN amount ELSE 0 END), 0) AS today,
                COALESCE(SUM(CASE WHEN DATE(payment_date) = ? THEN amount ELSE 0 END), 0) AS yesterday,
                COALESCE(SUM(CASE WHEN payment_date BETWEEN ? AND ? THEN amount ELSE 0 END), 0) AS this_month,
                COALESCE(SUM(CASE WHEN payment_date BETWEEN ? AND ? THEN amount ELSE 0 END), 0) AS last_month
            ", [$today, $yesterday, $thisMonthStart, $thisMonthEnd, $lastMonthStart, $lastMonthEnd])
            ->first();

        $totalFeeToday = (float) ($feePaymentStats->today ?? 0);

        $feeCollectedThisDay = (float) ($feePaymentStats->today ?? 0);
        $feeCollectedLastDay = (float) ($feePaymentStats->yesterday ?? 0);
        $trendFeeToday       = $this->percentChange($feeCollectedThisDay, $feeCollectedLastDay);

        $feeCollectedThisMonth = (float) ($feePaymentStats->this_month ?? 0);
        $feeCollectedLastMonth = (float) ($feePaymentStats->last_month ?? 0);
        $trendFeeCollected     = $this->percentChange($feeCollectedThisMonth, $feeCollectedLastMonth);

        $feeDueStats = DB::table('fee_invoices')
            ->where('institution_id', $institutionId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN invoice_date BETWEEN ? AND ? THEN due_amount ELSE 0 END), 0) AS this_month,
                COALESCE(SUM(CASE WHEN invoice_date BETWEEN ? AND ? THEN due_amount ELSE 0 END), 0) AS last_month
            ", [$thisMonthStart, $thisMonthEnd, $lastMonthStart, $lastMonthEnd])
            ->first();

        $feeDueThisMonth = (float) ($feeDueStats->this_month ?? 0);
        $feeDueLastMonth = (float) ($feeDueStats->last_month ?? 0);
        $trendFeeDue     = $this->percentChange($feeDueThisMonth, $feeDueLastMonth);

        // ── Office Accounts ────────────────────────────────────────────────
        $openingBalance = (float) DB::table('office_accounts')
            ->where('institution_id', $institutionId)
            ->sum('opening_balance');

        $totalDeposits = (float) DB::table('office_deposits')
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $totalExpenses = (float) DB::table('office_expenses')
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $accountBalance = $openingBalance + $totalDeposits - $totalExpenses;

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

        $salaryPaidThisMonth   = (float) ($salaryStats->paid   ?? 0);
        $salaryUnpaidThisMonth = (float) ($salaryStats->unpaid ?? 0);

        // ── Inventory Sales Today ──────────────────────────────────────────
        $inventorySalesToday = (float) DB::table('inventory_sales')
            ->where('institution_id', $institutionId)
            ->whereDate('date', $today)
            ->sum('net_payable');

        // ── Recent Invoices ────────────────────────────────────────────────
        $recentInvoices = DB::table('fee_invoices as fi')
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
        $recentPayments = DB::table('fee_payments as fp')
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
        $recentNotices = DB::table('notices')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->select('id', 'title', 'audience', 'priority', 'published_at')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        // ── Recent Messages ────────────────────────────────────────────────
        $recentMessages = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.receiver_id', auth()->id())
            ->where('m.is_deleted_by_receiver', false)
            ->select('m.id', 'u.name as sender_name', 'm.subject', 'm.is_read', 'm.created_at')
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get();

        // ── Recent Activities ──────────────────────────────────────────────
        $recentActivities = DB::table('activity_log')
            ->where('institution_id', $institutionId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'description', 'properties', 'created_at')
            ->get()
            ->map(function ($act) {
                $props = json_decode($act->properties, true);
                return [
                    'id'          => $act->id,
                    'description' => $act->description,
                    'icon'        => $props['icon'] ?? 'notifications',
                    'created_at'  => $act->created_at,
                ];
            });

        // ── Today's Birthdays ──────────────────────────────────────────────
        $todayMD = $today->format('m-d');

        $studentBirthdays = DB::table('students')
            ->where('institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [$todayMD])
            ->select('name', DB::raw("'Student' as role"))
            ->get();

        $employeeBirthdays = DB::table('employees as e')
            ->leftJoin('employee_designations as d', 'd.id', '=', 'e.designation_id')
            ->where('e.institution_id', $institutionId)
            ->whereRaw("DATE_FORMAT(e.dob, '%m-%d') = ?", [$todayMD])
            ->select('e.name', DB::raw("COALESCE(d.name, 'Staff') as role"))
            ->get();

        $todayBirthdays = $studentBirthdays->merge($employeeBirthdays)->take(5)->values();

        // ── Monthly Fee Collection (last 6 months) ──────────────────────────
        $monthlyFeeChart = DB::table('fee_payments')
            ->where('institution_id', $institutionId)
            ->where('payment_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, COALESCE(SUM(amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => Carbon::createFromFormat('Y-m', $row->month)->format('M Y'),
                'total' => (float) $row->total,
            ])
            ->values();

        // ── Final Response Payload ───────────────────────────────────────────
        return [
            'totals' => [
                'students'         => $totalStudents,
                'teachers'         => $totalTeachers,
                'employees'        => $totalEmployees,
                'classes'          => $totalClasses,
                'active_notices'   => $activeNotices,
                'unread_messages'  => $unreadMessages,
            ],
            'finance' => [
                'total_fee_collected' => $totalFeeCollected,
                'total_fee_due'       => $totalFeeDue,
                'total_fee_today'     => $totalFeeToday,
                'total_deposits'      => $totalDeposits,
                'total_expenses'      => $totalExpenses,
                'account_balance'     => $accountBalance,
            ],
            'salary' => [
                'paid_this_month'   => $salaryPaidThisMonth,
                'unpaid_this_month' => $salaryUnpaidThisMonth,
            ],
            'attendance' => [
                'students_present_today'  => $studentsPresentToday,
                'students_absent_today'   => $studentsAbsentToday,
                'employees_present_today' => $employeesPresentToday,
                'attendance_percent'      => $attendancePercent,
            ],
            'new_admissions_this_month' => $newAdmissionsThisMonth,
            'pending_homework'          => $pendingHomework,
            'exams' => [
                'upcoming'  => $upcomingExams,
                'today'     => $todayExams,
                'completed' => $completedExams,
            ],
            'inventory_sales_today' => $inventorySalesToday,
            'trends' => [
                'students'      => $trendStudents,
                'staffs'        => $trendStaffs,
                'teachers'      => $trendTeachers,
                'attendance'    => $trendAttendance,
                'admissions'    => $trendAdmissions,
                'homework'      => $trendHomework,
                'fee_collected' => $trendFeeCollected,
                'fee_today'     => $trendFeeToday,
                'fee_due'       => $trendFeeDue,
            ],
            'recent_invoices'   => $recentInvoices,
            'recent_payments'   => $recentPayments,
            'recent_notices'    => $recentNotices,
            'recent_messages'   => $recentMessages,
            'recent_activities' => $recentActivities,
            'today_birthdays'   => $todayBirthdays,
            'monthly_fee_chart' => $monthlyFeeChart,
        ];
    }

    /**
     * Duita number theke % Change ar Direction (up/down) ber kore.
     * $previous = 0 hole current > 0 hole 100% up dhora hoy, duitai 0 hole 0%.
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
}
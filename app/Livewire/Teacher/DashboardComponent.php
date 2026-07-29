<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\ExamSchedule;
use App\Models\AcademicClassAssignDetail;

class DashboardComponent extends Component
{
    // ── Teacher Info ───────────────────────────────────────────────────────
    public string $teacherName     = '';
    public ?int   $teacherId       = null;
    public ?int   $institutionId   = null;

    // ── My Classes ─────────────────────────────────────────────────────────
    public int   $myTotalClasses   = 0;
    public int   $myTotalStudents  = 0;

    // ── My Attendance (today) ──────────────────────────────────────────────
    public ?string $myAttendanceToday = null;   // 'present' | 'absent' | 'leave' | null

    // ── My Students Attendance (today) ────────────────────────────────────
    public int   $myStudentsPresentToday = 0;
    public int   $myStudentsAbsentToday  = 0;
    public float $myAttendancePercent    = 0;

    // ── My Leave ──────────────────────────────────────────────────────────
    public int   $myPendingLeave   = 0;
    public int   $myApprovedLeave  = 0;
    public int   $myRejectedLeave  = 0;

    // ── My Homework ───────────────────────────────────────────────────────
    public int   $myPendingHomework   = 0;
    public int   $myTotalHomework     = 0;

    // ── Upcoming Exams ────────────────────────────────────────────────────
    public int   $upcomingExams    = 0;

    // ── Notices & Messages ────────────────────────────────────────────────
    public int   $activeNotices    = 0;
    public int   $unreadMessages   = 0;

    // ── Today's Birthdays ────────────────────────────────────────────────
    public $todayBirthdays;

    // ── Recent Notices ────────────────────────────────────────────────────
    public $recentNotices;

    // ── Recent Messages ───────────────────────────────────────────────────
    public $recentMessages;

    // ── My Recent Leave Applications ──────────────────────────────────────
    public $myRecentLeaves;

    // ── Recent Activities ─────────────────────────────────────────────────
    public $recentActivities;

    // ── Monthly Attendance Chart (my students, last 6 months) ────────────
    public array $monthlyAttendanceChart = [];

    public function mount(): void
    {
        $user                 = auth()->user();
        $this->teacherName    = $user->name;
        $this->teacherId      = $user->id;
        $this->institutionId  = $user->institution_id;

        $schoolId = $this->institutionId;
        $today    = Carbon::today();
        $todayMD  = $today->format('m-d');

        // ── Resolve Employee ID (users → employees) ────────────────────────
        // employee attendance/leave er jonno employee id lagbe (attendances.attendable_id = employees.id)
        // kintu class/section assign ekhon academic_class_assign_details theke ashbe, jeta
        // sরাসরি teacher_id = users.id use kore, employee id lagbe na
        $myEmployeeId = DB::table('employees')
            ->where('institution_id', $schoolId)
            ->where('user_id', $this->teacherId)
            ->value('id');

        // ── My Classes & Students ──────────────────────────────────────────
        // academic_class_assign_details.teacher_id = users.id (auth()->id() direct compare)
        $myAssigns = DB::table('academic_class_assign_details as d')
            ->join('academic_class_assigns as a', 'a.id', '=', 'd.academic_class_assign_id')
            ->where('a.institution_id', $schoolId)
            ->where('d.teacher_id', $this->teacherId)
            ->select('a.class_id', 'a.section_id')
            ->distinct()
            ->get();

        $this->myTotalClasses = $myAssigns->count();

        // NOTE (bug fix — DRY / duplicate query): the old code ran this exact
        // "which students belong to my classes" lookup TWICE — once here
        // (just to get a count) and again below (to get the actual ids for
        // attendance/birthdays/chart). Both used byte-identical dynamic
        // where-closures. Now it's built once and reused everywhere.
        $myStudentIds = collect();

        if ($myAssigns->isNotEmpty()) {
            $myStudentIds = DB::table('students')
                ->where('institution_id', $schoolId)
                ->where(function ($q) use ($myAssigns) {
                    foreach ($myAssigns as $assign) {
                        $q->orWhere(function ($inner) use ($assign) {
                            $inner->where('class_id', $assign->class_id);

                            if (!empty($assign->section_id)) {
                                $inner->where('section_id', $assign->section_id);
                            }
                        });
                    }
                })
                ->pluck('id');

            $this->myTotalStudents = $myStudentIds->count();
        }

        // ── My Attendance Today ────────────────────────────────────────────
        // employee attendance — attendable_id = employees.id (eta age-er moto thake)
        $myAttendance = $myEmployeeId
            ? DB::table('attendances')
                ->where('institution_id', $schoolId)
                ->where('type', 'employee')
                ->where('attendable_id', $myEmployeeId)
                ->whereDate('date', $today)
                ->value('status')
            : null;

        $this->myAttendanceToday = $myAttendance ?? null;

        // ── My Students Attendance Today ───────────────────────────────────
        if ($myStudentIds->isNotEmpty()) {

            $totalMarked = DB::table('attendances')
                ->where('institution_id', $schoolId)
                ->where('type', 'student')
                ->whereIn('attendable_id', $myStudentIds)
                ->whereDate('date', $today)
                ->count();

            $this->myStudentsPresentToday = DB::table('attendances')
                ->where('institution_id', $schoolId)
                ->where('type', 'student')
                ->whereIn('attendable_id', $myStudentIds)
                ->whereDate('date', $today)
                ->where('status', 'present')
                ->count();

            $this->myStudentsAbsentToday = DB::table('attendances')
                ->where('institution_id', $schoolId)
                ->where('type', 'student')
                ->whereIn('attendable_id', $myStudentIds)
                ->whereDate('date', $today)
                ->where('status', 'absent')
                ->count();

            $this->myAttendancePercent = $totalMarked > 0
                ? round(($this->myStudentsPresentToday / $totalMarked) * 100, 1)
                : 0;
        }

        // ── My Leave Applications ──────────────────────────────────────────
        $leaveBase = DB::table('leave_applications')
            ->where('applicable_id', $this->teacherId)
            ->where('applicable_type', User::class);

        $this->myPendingLeave  = (clone $leaveBase)->where('status', 'pending')->count();
        $this->myApprovedLeave = (clone $leaveBase)->where('status', 'approved')->count();
        $this->myRejectedLeave = (clone $leaveBase)->where('status', 'rejected')->count();

        // ── My Recent Leave Applications ───────────────────────────────────
        $this->myRecentLeaves = DB::table('leave_applications as la')
            ->leftJoin('leave_categories as lc', 'lc.id', '=', 'la.leave_category_id')
            ->where('la.applicable_id', $this->teacherId)
            ->where('la.applicable_type', User::class)
            ->select('la.id', 'lc.name as category', 'la.start_date', 'la.end_date', 'la.total_days', 'la.status', 'la.created_at')
            ->orderByDesc('la.created_at')
            ->limit(5)
            ->get();

        // ── My Homework ─────────────────────────────────────────────────────
        // NOTE (bug fix): $myTotalHomework / $myPendingHomework were declared
        // as public properties but never actually assigned anywhere — the
        // dashboard cards always showed 0 regardless of real data.
        // "Pending" = still open for students to submit (published + the
        // submission deadline hasn't passed yet).
        $this->myTotalHomework = DB::table('homeworks')
            ->where('institution_id', $schoolId)
            ->where('teacher_id', $this->teacherId)
            ->count();

        $this->myPendingHomework = DB::table('homeworks')
            ->where('institution_id', $schoolId)
            ->where('teacher_id', $this->teacherId)
            ->where('status', 'published')
            ->whereDate('submission_date', '>=', $today)
            ->count();

        // ── Upcoming Exams ───────────────────────────────────────────────────
        // NOTE (bug fix): $upcomingExams was declared but never assigned —
        // always 0. Count published exam schedules, on/after today, for the
        // subjects this teacher actually teaches (same
        // AcademicClassAssignDetail scoping used by the Exam module).
        $myAssignIds = AcademicClassAssignDetail::where('teacher_id', $this->teacherId)
            ->pluck('academic_class_assign_id');

        $this->upcomingExams = ExamSchedule::where('institution_id', $schoolId)
            ->where('is_published', true)
            ->where('exam_date', '>=', $today)
            ->whereHas('examSetup', fn($q) => $q->where('is_published', true))
            ->whereHas(
                'examSetupDetail.classAssignDetail',
                fn($q) => $q->whereIn('academic_class_assign_id', $myAssignIds)
            )
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
            ->where('institution_id', $schoolId)
            ->where('receiver_id', $this->teacherId)
            ->where('is_read', false)
            ->where('is_trashed_by_receiver', false)
            ->where('is_deleted_by_receiver', false)
            ->count();

        // ── Recent Notices ─────────────────────────────────────────────────
        // NOTE (bug fix): this used to skip the published_at/expires_at
        // check that $activeNotices above already applies — a notice
        // scheduled for the future, or one that already expired, could show
        // up in the "recent notices" widget before/after it should be
        // visible to teachers.
        $this->recentNotices = DB::table('notices')
            ->where('institution_id', $schoolId)
            ->where('status', 'active')
            ->where('published_at', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $today);
            })
            ->where(function ($q) {
                $q->whereNull('audience')
                  ->orWhere('audience', 'all')
                  ->orWhere('audience', 'teacher');
            })
            ->select('id', 'title', 'audience', 'priority', 'published_at')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        // ── Recent Messages ────────────────────────────────────────────────
        $this->recentMessages = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->where('m.institution_id', $schoolId)
            ->where('m.receiver_id', $this->teacherId)
            ->where('m.is_deleted_by_receiver', false)
            ->select('m.id', 'u.name as sender_name', 'm.subject', 'm.is_read', 'm.created_at')
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get();

        // ── Recent Activities ──────────────────────────────────────────────
        $this->recentActivities = DB::table('activity_log')
            ->where('institution_id', $schoolId)
            ->where('causer_id', $this->teacherId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'description', 'properties', 'created_at')
            ->get()
            ->map(function ($act) {
                $props      = json_decode($act->properties, true);
                $act->icon  = $props['icon'] ?? 'notifications';
                return $act;
            });

        // ── Today's Birthdays (students from my classes + teachers) ───────
        $studentBirthdays = $myStudentIds->isNotEmpty()
            ? DB::table('students')
                ->whereIn('id', $myStudentIds)
                ->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [$todayMD])
                ->select('name', DB::raw("'Student' as role"))
                ->get()
            : collect();

        $teacherBirthdays = DB::table('employees')
            ->where('institution_id', $schoolId)
            ->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [$todayMD])
            ->select('name', DB::raw("'Teacher' as role"))
            ->get();

        $this->todayBirthdays = $studentBirthdays->merge($teacherBirthdays)->take(5);

        // ── Monthly Student Attendance Chart (my students, last 6 months) ──
        if ($myStudentIds->isNotEmpty()) {
            $this->monthlyAttendanceChart = DB::table('attendances')
                ->where('institution_id', $schoolId)
                ->where('type', 'student')
                ->whereIn('attendable_id', $myStudentIds)
                ->where('date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
                ->selectRaw("
                    DATE_FORMAT(date, '%Y-%m') as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                ")
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(fn($row) => [
                    'month'   => Carbon::createFromFormat('Y-m', $row->month)->format('M Y'),
                    'percent' => $row->total > 0 ? round(($row->present / $row->total) * 100, 1) : 0,
                ])
                ->toArray();
        }
    }

    public function render()
    {
        return view('livewire.teacher.dashboard-component')
            ->layout('layouts.teacher.app', [
                'title' => 'Dashboard | ' . institution()->name,
            ]);
    }
}
<?php

namespace App\Services\Ministry\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Central query service for Ministry Reports & Analytics.
 * Single source of truth — Statistics/Ranking/Academic Livewire components
 * should eventually refactor to reuse these methods instead of duplicating queries.
 */
class ReportDataService
{
    public const RATIO_FLAG_THRESHOLD = 40;
    public const PASS_RATE_FLAG_THRESHOLD = 40;
    public const MIN_RESULTS_FOR_RANKING = 10;

    public const GPA_SUB_WEIGHT = 0.6;
    public const PASS_RATE_SUB_WEIGHT = 0.4;
    public const ACADEMIC_WEIGHT = 1.0;
    public const COMPLIANCE_WEIGHT = 0.0;

    /* ---------------------------------------------------------------
     | Student Statistics
     |---------------------------------------------------------------*/
    public function studentStatistics(?string $division = null): array
    {
        $base = DB::table('student_enrollments as se')
            ->join('students as s', 's.id', '=', 'se.student_id')
            ->join('institutions as i', 'i.id', '=', 'se.institution_id')
            ->where('se.status', 'running');

        if ($division) {
            $base->where('i.division', $division);
        }

        $total = (clone $base)->count();

        $genderBreakdown = (clone $base)
            ->select('s.gender', DB::raw('COUNT(*) as total'))
            ->groupBy('s.gender')
            ->get();

        $classBreakdown = (clone $base)
            ->join('academic_classes as ac', 'ac.id', '=', 'se.class_id')
            ->select('ac.id', 'ac.name', DB::raw('COUNT(*) as total'))
            ->groupBy('ac.id', 'ac.name', 'ac.numeric')
            ->orderBy('ac.numeric')
            ->get();

        $divisionBreakdown = (clone $base)
            ->select('i.division', DB::raw('COUNT(*) as total'))
            ->groupBy('i.division')
            ->orderByDesc('total')
            ->get();

        return [
            'total' => $total,
            'gender_breakdown' => $genderBreakdown,
            'class_breakdown' => $classBreakdown,
            'division_breakdown' => $divisionBreakdown,
        ];
    }

    /* ---------------------------------------------------------------
     | Teacher Statistics
     |---------------------------------------------------------------*/
    public function teacherStatistics(?string $division = null): array
    {
        $teacherBase = DB::table('employees as e')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->join('institutions as i', 'i.id', '=', 'e.institution_id')
            ->whereNull('e.deleted_at')
            ->where('u.role', 'teacher')
            ->where('u.is_active', true);

        if ($division) {
            $teacherBase->where('i.division', $division);
        }

        $totalTeachers = (clone $teacherBase)->count();

        $subjectDistribution = DB::table('academic_class_assign_details as acad')
            ->join('academic_subjects as sub', 'sub.id', '=', 'acad.subject_id')
            ->join('institutions as i', 'i.id', '=', 'acad.institution_id')
            ->whereNotNull('acad.teacher_id')
            ->when($division, fn ($q) => $q->where('i.division', $division))
            ->select('sub.id', 'sub.name', DB::raw('COUNT(DISTINCT acad.teacher_id) as teacher_count'))
            ->groupBy('sub.id', 'sub.name')
            ->orderByDesc('teacher_count')
            ->get();

        // student & teacher counts fetched separately per institution then merged
        // (joining directly inflates counts due to fan-out — documented lesson from Statistics module)
        $studentCounts = DB::table('student_enrollments as se')
            ->join('institutions as i', 'i.id', '=', 'se.institution_id')
            ->where('se.status', 'running')
            ->when($division, fn ($q) => $q->where('i.division', $division))
            ->select('i.id', 'i.name', DB::raw('COUNT(*) as student_count'))
            ->groupBy('i.id', 'i.name')
            ->get()
            ->keyBy('id');

        $teacherCounts = (clone $teacherBase)
            ->select('i.id', 'i.name', DB::raw('COUNT(DISTINCT e.id) as teacher_count'))
            ->groupBy('i.id', 'i.name')
            ->get()
            ->keyBy('id');

        $ratios = $studentCounts->map(function ($row) use ($teacherCounts) {
            $teacherCount = $teacherCounts[$row->id]->teacher_count ?? 0;
            $ratio = $teacherCount > 0 ? round($row->student_count / $teacherCount, 1) : null;

            return (object) [
                'institution_id' => $row->id,
                'institution_name' => $row->name,
                'student_count' => $row->student_count,
                'teacher_count' => $teacherCount,
                'ratio' => $ratio,
                'flagged' => $ratio !== null && $ratio > self::RATIO_FLAG_THRESHOLD,
            ];
        })->values();

        return [
            'total_teachers' => $totalTeachers,
            'subject_distribution' => $subjectDistribution,
            'ratios' => $ratios,
        ];
    }

    /* ---------------------------------------------------------------
     | Institution List
     |---------------------------------------------------------------*/
    public function institutionList(?string $verificationStatus = null, ?string $division = null): \Illuminate\Support\Collection
    {
        return DB::table('institutions')
            ->select('id', 'name', 'eiin', 'division', 'district', 'status', 'verification_status', 'verified_at')
            ->when($verificationStatus, fn ($q) => $q->where('verification_status', $verificationStatus))
            ->when($division, fn ($q) => $q->where('division', $division))
            ->orderBy('division')
            ->orderBy('name')
            ->get();
    }

    /* ---------------------------------------------------------------
     | Academic Performance
     |---------------------------------------------------------------*/
    protected function publishedExamResultsQuery(?string $division, ?int $academicSessionId)
    {
        $query = DB::table('exam_positions as ep')
            ->join('exam_setups as es', 'es.id', '=', 'ep.exam_setup_id')
            ->join('institutions as i', 'i.id', '=', 'ep.institution_id')
            ->where('es.is_result_published', true)
            ->whereNull('ep.deleted_at');

        if ($division) {
            $query->where('i.division', $division);
        }
        if ($academicSessionId) {
            $query->where('ep.academic_session_id', $academicSessionId);
        }

        return $query;
    }

    public function academicPerformance(?string $division = null, ?int $academicSessionId = null): array
    {
        $base = $this->publishedExamResultsQuery($division, $academicSessionId);

        $summary = (clone $base)
            ->selectRaw('COUNT(DISTINCT ep.exam_setup_id) as published_exams')
            ->selectRaw('COUNT(*) as results_evaluated')
            ->selectRaw("SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count")
            ->selectRaw('AVG(ep.gpa) as avg_gpa')
            ->selectRaw('AVG(ep.percentage) as avg_percentage')
            ->first();

        $passRate = $summary->results_evaluated > 0
            ? round(($summary->pass_count / $summary->results_evaluated) * 100, 2)
            : 0;

        $statusBreakdown = (clone $base)
            ->select('ep.result', DB::raw('COUNT(*) as total'))
            ->groupBy('ep.result')
            ->get();

        $divisionWise = (clone $base)
            ->select('i.division')
            ->selectRaw('COUNT(*) as total_results')
            ->selectRaw("SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count")
            ->selectRaw('AVG(ep.gpa) as avg_gpa')
            ->selectRaw('AVG(ep.percentage) as avg_percentage')
            ->groupBy('i.division')
            ->get()
            ->map(function ($row) {
                $row->pass_rate = $row->total_results > 0
                    ? round(($row->pass_count / $row->total_results) * 100, 2)
                    : 0;
                $row->flagged = $row->pass_rate < self::PASS_RATE_FLAG_THRESHOLD;

                return $row;
            });

        $institutionWise = (clone $base)
            ->select('i.id', 'i.name', 'i.division')
            ->selectRaw('COUNT(*) as total_results')
            ->selectRaw("SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count")
            ->selectRaw('AVG(ep.gpa) as avg_gpa')
            ->selectRaw('AVG(ep.percentage) as avg_percentage')
            ->groupBy('i.id', 'i.name', 'i.division')
            ->get()
            ->map(function ($row) {
                $row->pass_rate = $row->total_results > 0
                    ? round(($row->pass_count / $row->total_results) * 100, 2)
                    : 0;
                $row->flagged = $row->pass_rate < self::PASS_RATE_FLAG_THRESHOLD;

                return $row;
            });

        return [
            'published_exams' => $summary->published_exams ?? 0,
            'results_evaluated' => $summary->results_evaluated ?? 0,
            'pass_rate' => $passRate,
            'avg_gpa' => round($summary->avg_gpa ?? 0, 2),
            'status_breakdown' => $statusBreakdown,
            'division_wise' => $divisionWise,
            'institution_wise' => $institutionWise,
        ];
    }

    /* ---------------------------------------------------------------
     | Institution Ranking (Academic-only, provisional)
     |---------------------------------------------------------------*/
    public function ranking(?string $division = null, ?int $academicSessionId = null): \Illuminate\Support\Collection
    {
        $performance = $this->academicPerformance($division, $academicSessionId)['institution_wise'];

        return collect($performance)
            ->filter(fn ($row) => $row->total_results >= self::MIN_RESULTS_FOR_RANKING)
            ->map(function ($row) {
                $gpaScore = min(($row->avg_gpa ?? 0) / 5, 1) * 100;
                $academicScore = ($gpaScore * self::GPA_SUB_WEIGHT) + ($row->pass_rate * self::PASS_RATE_SUB_WEIGHT);
                $complianceScore = 0.0; // placeholder — Compliance module not yet built
                $finalScore = ($academicScore * self::ACADEMIC_WEIGHT) + ($complianceScore * self::COMPLIANCE_WEIGHT);

                $row->gpa_score = round($gpaScore, 2);
                $row->academic_score = round($academicScore, 2);
                $row->final_score = round($finalScore, 2);

                return $row;
            })
            ->sortByDesc('final_score')
            ->values()
            ->map(function ($row, $index) {
                $row->rank = $index + 1;

                return $row;
            });
    }
}
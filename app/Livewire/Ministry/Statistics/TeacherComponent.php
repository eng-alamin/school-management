<?php

declare(strict_types=1);

namespace App\Livewire\Ministry\Statistics;

use App\Models\Institution;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class TeacherComponent extends Component
{
    #[Url(history: true)]
    public string $division = '';

    /**
     * Teacher-Student ratio threshold. Ratio er theke beshi hole
     * institution ta "high ratio" hisebe flag hobe.
     */
    private const RATIO_FLAG_THRESHOLD = 40;

    public function resetFilters(): void
    {
        $this->division = '';
    }

    public function getDivisions(): array
    {
        return Institution::DIVISIONS;
    }

    /**
     * Common base query for active teachers (Employee jar user role = teacher).
     */
    private function teacherBaseQuery(): \Illuminate\Database\Query\Builder
    {
        $query = DB::table('employees as e')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->join('institutions as i', 'e.institution_id', '=', 'i.id')
            ->where('u.role', 'teacher')
            ->where('u.is_active', true)
            ->whereNull('e.deleted_at');

        if ($this->division !== '') {
            $query->where('i.division', $this->division);
        }

        return $query;
    }

    private function totalTeachers(): int
    {
        return $this->teacherBaseQuery()->count();
    }

    private function subjectWiseDistribution(): Collection
    {
        $query = DB::table('academic_class_assign_details as acad')
            ->join('academic_subjects as sub', 'acad.subject_id', '=', 'sub.id')
            ->join('institutions as i', 'acad.institution_id', '=', 'i.id')
            ->whereNotNull('acad.teacher_id');

        if ($this->division !== '') {
            $query->where('i.division', $this->division);
        }

        return $query
            ->select('sub.id', 'sub.name', DB::raw('count(distinct acad.teacher_id) as teacher_count'))
            ->groupBy('sub.id', 'sub.name')
            ->orderByDesc('teacher_count')
            ->get();
    }

    /**
     * Institution wise Teacher-Student ratio.
     * Student o Teacher count alada query kore, tarpor merge kora hoyeche
     * karon dutoi group-by aggregate — ekta query te korle join-count vul asbe.
     */
    private function institutionRatios(): Collection
    {
        $studentCounts = DB::table('student_enrollments as se')
            ->join('institutions as i', 'se.institution_id', '=', 'i.id')
            ->where('se.status', 'running')
            ->when($this->division !== '', fn ($q) => $q->where('i.division', $this->division))
            ->select('i.id as institution_id', 'i.name as institution_name', DB::raw('count(*) as student_total'))
            ->groupBy('i.id', 'i.name')
            ->get()
            ->keyBy('institution_id');

        $teacherCounts = $this->teacherBaseQuery()
            ->select('i.id as institution_id', DB::raw('count(*) as teacher_total'))
            ->groupBy('i.id')
            ->get()
            ->keyBy('institution_id');

        return $studentCounts->map(function ($row) use ($teacherCounts) {
            $teacherTotal = (int) ($teacherCounts->get($row->institution_id)->teacher_total ?? 0);
            $ratio = $teacherTotal > 0 ? round($row->student_total / $teacherTotal, 1) : null;

            return (object) [
                'institution_id' => $row->institution_id,
                'institution_name' => $row->institution_name,
                'student_total' => $row->student_total,
                'teacher_total' => $teacherTotal,
                'ratio' => $ratio,
                'is_flagged' => $ratio !== null && $ratio > self::RATIO_FLAG_THRESHOLD,
            ];
        })->sortByDesc(fn ($row) => $row->ratio ?? -1)->values();
    }

    public function render()
    {
        return view('livewire.ministry.statistics.teacher-component', [
            'divisions' => $this->getDivisions(),
            'totalTeachers' => $this->totalTeachers(),
            'subjectWiseDistribution' => $this->subjectWiseDistribution(),
            'institutionRatios' => $this->institutionRatios(),
        ])
        ->layout('layouts.ministry.app', [
            'title' => 'Teacher Statistics | ' . setting('app_name', 'EMS'),
        ]);
    }
}
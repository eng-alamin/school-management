<?php

namespace App\Livewire\Ministry\Academic;

use App\Livewire\Ministry\Concerns\QueriesPublishedExamResults;
use Livewire\Attributes\Url;
use Livewire\Component;

class PerformanceComponent extends Component
{
    use QueriesPublishedExamResults;

    /**
     * Institutions with a pass rate below this percentage are flagged
     * for Ministry attention. Adjust here if the threshold policy changes.
     */
    public const PASS_RATE_FLAG_THRESHOLD = 40;

    #[Url(history: true)]
    public ?string $division = null;

    #[Url(history: true)]
    public ?int $academicSessionId = null;

    #[Url(history: true)]
    public string $sortBy = 'pass_rate';

    #[Url(history: true)]
    public string $sortDirection = 'asc';

    public function mount(): void
    {
        $this->defaultToLatestAcademicSession();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function getSummaryProperty(): array
    {
        $row = $this->publishedExamResultsQuery()
            ->selectRaw("
                COUNT(*) as total_results,
                COUNT(DISTINCT ep.exam_setup_id) as total_exams,
                SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count,
                SUM(CASE WHEN ep.result = 'fail' THEN 1 ELSE 0 END) as fail_count,
                SUM(CASE WHEN ep.result = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN ep.result = 'incomplete' THEN 1 ELSE 0 END) as incomplete_count,
                AVG(ep.gpa) as avg_gpa,
                AVG(ep.percentage) as avg_percentage
            ")
            ->first();

        $total = (int) ($row->total_results ?? 0);
        $passCount = (int) ($row->pass_count ?? 0);

        return [
            'total_results'    => $total,
            'total_exams'      => (int) ($row->total_exams ?? 0),
            'pass_count'       => $passCount,
            'fail_count'       => (int) ($row->fail_count ?? 0),
            'absent_count'     => (int) ($row->absent_count ?? 0),
            'incomplete_count' => (int) ($row->incomplete_count ?? 0),
            'pass_rate'        => $total > 0 ? round($passCount / $total * 100, 2) : 0.0,
            'avg_gpa'          => $row->avg_gpa !== null ? round((float) $row->avg_gpa, 2) : null,
            'avg_percentage'   => $row->avg_percentage !== null ? round((float) $row->avg_percentage, 2) : null,
        ];
    }

    public function getDivisionBreakdownProperty()
    {
        return $this->publishedExamResultsQuery()
            ->selectRaw("
                i.division as division,
                COUNT(*) as total_results,
                SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count,
                SUM(CASE WHEN ep.result = 'fail' THEN 1 ELSE 0 END) as fail_count,
                SUM(CASE WHEN ep.result = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN ep.result = 'incomplete' THEN 1 ELSE 0 END) as incomplete_count,
                AVG(ep.gpa) as avg_gpa,
                AVG(ep.percentage) as avg_percentage
            ")
            ->groupBy('i.division')
            ->orderBy('i.division')
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total_results;
                $pass = (int) $row->pass_count;
                $row->pass_rate = $total > 0 ? round($pass / $total * 100, 2) : 0.0;
                $row->avg_gpa = $row->avg_gpa !== null ? round((float) $row->avg_gpa, 2) : null;
                $row->avg_percentage = $row->avg_percentage !== null ? round((float) $row->avg_percentage, 2) : null;
                $row->is_flagged = $row->pass_rate < self::PASS_RATE_FLAG_THRESHOLD;

                return $row;
            });
    }

    public function getInstitutionBreakdownProperty()
    {
        $rows = $this->publishedExamResultsQuery()
            ->selectRaw("
                i.id as institution_id,
                i.name as institution_name,
                i.eiin as eiin,
                i.division as division,
                COUNT(*) as total_results,
                SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count,
                SUM(CASE WHEN ep.result = 'fail' THEN 1 ELSE 0 END) as fail_count,
                SUM(CASE WHEN ep.result = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN ep.result = 'incomplete' THEN 1 ELSE 0 END) as incomplete_count,
                AVG(ep.gpa) as avg_gpa,
                AVG(ep.percentage) as avg_percentage
            ")
            ->groupBy('i.id', 'i.name', 'i.eiin', 'i.division')
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total_results;
                $pass = (int) $row->pass_count;
                $row->pass_rate = $total > 0 ? round($pass / $total * 100, 2) : 0.0;
                $row->avg_gpa = $row->avg_gpa !== null ? round((float) $row->avg_gpa, 2) : null;
                $row->avg_percentage = $row->avg_percentage !== null ? round((float) $row->avg_percentage, 2) : null;
                $row->is_flagged = $row->pass_rate < self::PASS_RATE_FLAG_THRESHOLD;

                return $row;
            });

        return $this->sortDirection === 'desc'
            ? $rows->sortByDesc($this->sortBy)->values()
            : $rows->sortBy($this->sortBy)->values();
    }

    public function render()
    {
        return view('livewire.ministry.academic.performance-component', [
            'divisions'            => $this->divisions,
            'academicSessions'     => $this->academicSessions,
            'summary'              => $this->summary,
            'divisionBreakdown'    => $this->divisionBreakdown,
            'institutionBreakdown' => $this->institutionBreakdown,
            'passRateThreshold'    => self::PASS_RATE_FLAG_THRESHOLD,
        ])
        ->layout('layouts.ministry.app', [
            'title' => 'Academic Performance | ' . setting('app_name', 'EMS'),
        ]);
    }
}

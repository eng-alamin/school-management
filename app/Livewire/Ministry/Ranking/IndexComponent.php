<?php

namespace App\Livewire\Ministry\Ranking;

use App\Livewire\Ministry\Concerns\QueriesPublishedExamResults;
use Livewire\Attributes\Url;
use Livewire\Component;

class IndexComponent extends Component
{
    use QueriesPublishedExamResults;

    /**
     * Weight of the Academic score within the final ranking score.
     * Currently 1.0 (100%) because the Compliance & Inspection module
     * (Phase 2) has no scoring implementation yet — only the roadmap
     * status was marked complete, actual migrations/components were
     * never written. Once compliance scoring exists:
     *   1. Change these two constants (e.g. 0.6 / 0.4)
     *   2. Replace the hardcoded $complianceScore = 0.0 below with a
     *      real query against institution_compliance_scores
     */
    public const ACADEMIC_WEIGHT = 1.0;
    public const COMPLIANCE_WEIGHT = 0.0; // reserved, not wired up yet

    /**
     * Within the Academic score itself: split between average GPA
     * (quality of results) and pass rate (breadth of success).
     */
    public const GPA_SUB_WEIGHT = 0.6;
    public const PASS_RATE_SUB_WEIGHT = 0.4;

    /**
     * Institutions with fewer than this many evaluated results are
     * excluded from ranking — too small a sample to be meaningful.
     */
    public const MIN_RESULTS_FOR_RANKING = 10;

    #[Url(history: true)]
    public ?string $division = null;

    #[Url(history: true)]
    public ?int $academicSessionId = null;

    public function mount(): void
    {
        $this->defaultToLatestAcademicSession();
    }

    public function getRankingProperty()
    {
        $rows = $this->publishedExamResultsQuery()
            ->selectRaw("
                i.id as institution_id,
                i.name as institution_name,
                i.eiin as eiin,
                i.division as division,
                COUNT(*) as total_results,
                SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count,
                AVG(ep.gpa) as avg_gpa
            ")
            ->groupBy('i.id', 'i.name', 'i.eiin', 'i.division')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_RESULTS_FOR_RANKING])
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total_results;
                $pass = (int) $row->pass_count;

                $passRate = $total > 0 ? ($pass / $total * 100) : 0.0;
                $avgGpa = $row->avg_gpa !== null ? (float) $row->avg_gpa : 0.0;

                // Normalize GPA (out of 5, Bangladesh scale) to a 0-100 score.
                $gpaScore = min($avgGpa / 5, 1) * 100;

                $academicScore = ($gpaScore * self::GPA_SUB_WEIGHT) + ($passRate * self::PASS_RATE_SUB_WEIGHT);

                // Placeholder until Compliance & Inspection scoring exists.
                // COMPLIANCE_WEIGHT is 0.0, so this has no effect yet.
                $complianceScore = 0.0;

                $finalScore = ($academicScore * self::ACADEMIC_WEIGHT) + ($complianceScore * self::COMPLIANCE_WEIGHT);

                $row->pass_rate = round($passRate, 2);
                $row->avg_gpa = round($avgGpa, 2);
                $row->academic_score = round($academicScore, 2);
                $row->final_score = round($finalScore, 2);

                return $row;
            })
            ->sortByDesc('final_score')
            ->values();

        return $rows->map(function ($row, $index) {
            $row->rank = $index + 1;

            return $row;
        });
    }

    public function render()
    {
        return view('livewire.ministry.ranking.index-component', [
            'divisions'        => $this->divisions,
            'academicSessions' => $this->academicSessions,
            'ranking'          => $this->ranking,
            'minResults'       => self::MIN_RESULTS_FOR_RANKING,
        ])
        ->layout('layouts.ministry.app', [
            'title' => 'Ranking | ' . setting('app_name', 'EMS'),
        ]);
    }
}

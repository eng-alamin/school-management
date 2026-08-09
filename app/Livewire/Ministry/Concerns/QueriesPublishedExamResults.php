<?php

namespace App\Livewire\Ministry\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Shared query logic for Ministry components that report on exam results.
 * Requires the using class to have public $division and $academicSessionId
 * properties.
 */
trait QueriesPublishedExamResults
{
    /**
     * Base query joining exam_positions (authoritative result summary) with
     * exam_setups (to filter published results only) and institutions
     * (to allow division filtering). No single-institution scoping here —
     * the Ministry panel intentionally reports across all institutions.
     */
    protected function publishedExamResultsQuery()
    {
        return DB::table('exam_positions as ep')
            ->join('exam_setups as es', 'es.id', '=', 'ep.exam_setup_id')
            ->join('institutions as i', 'i.id', '=', 'ep.institution_id')
            ->whereNull('ep.deleted_at')
            ->where('es.is_result_published', true)
            ->when($this->division, fn ($q) => $q->where('i.division', $this->division))
            ->when($this->academicSessionId, fn ($q) => $q->where('ep.academic_session_id', $this->academicSessionId));
    }

    public function getDivisionsProperty()
    {
        return DB::table('institutions')
            ->whereNotNull('division')
            ->distinct()
            ->orderBy('division')
            ->pluck('division');
    }

    public function getAcademicSessionsProperty()
    {
        return DB::table('academic_sessions')
            ->orderByDesc('id')
            ->get(['id', 'name']);
    }

    /**
     * Defaults academicSessionId to the latest session so the dashboard
     * is never blank on first load. Call from mount().
     */
    protected function defaultToLatestAcademicSession(): void
    {
        if ($this->academicSessionId === null) {
            $latest = DB::table('academic_sessions')->orderByDesc('id')->first();
            $this->academicSessionId = $latest->id ?? null;
        }
    }
}

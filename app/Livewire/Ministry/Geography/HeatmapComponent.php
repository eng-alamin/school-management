<?php

namespace App\Livewire\Ministry\Geography;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class HeatmapComponent extends Component
{
    public const METRICS = ['institutions', 'students', 'pass_rate'];
    public const LEVELS = ['division', 'district'];

    #[Url(history: true)]
    public string $metric = 'institutions';

    #[Url(history: true)]
    public string $level = 'division';

    #[Url(history: true)]
    public ?string $division = null; // narrows district-level view to one division

    #[Url(history: true)]
    public ?int $academicSessionId = null; // only used for pass_rate metric

    public function mount(): void
    {
        // Guard against invalid values arriving via URL query string.
        if (! in_array($this->metric, self::METRICS, true)) {
            $this->metric = 'institutions';
        }

        if (! in_array($this->level, self::LEVELS, true)) {
            $this->level = 'division';
        }

        if ($this->academicSessionId === null) {
            $latest = DB::table('academic_sessions')->orderByDesc('id')->first();
            $this->academicSessionId = $latest->id ?? null;
        }
    }

    public function updatedLevel(): void
    {
        if ($this->level === 'division') {
            $this->division = null;
        }
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
        return DB::table('academic_sessions')->orderByDesc('id')->get(['id', 'name']);
    }

    /**
     * Column to group by. Only ever 'division' or 'district' — validated
     * in mount() — before it is interpolated into raw SQL below.
     */
    protected function groupColumn(): string
    {
        return $this->level === 'district' ? 'district' : 'division';
    }

    public function getGridDataProperty()
    {
        $column = $this->groupColumn();

        $rows = match ($this->metric) {
            'students'  => $this->studentCountByColumn($column),
            'pass_rate' => $this->passRateByColumn($column),
            default     => $this->institutionCountByColumn($column),
        };

        $values = $rows->pluck('value')->filter(fn ($v) => $v !== null);
        $min = $values->min() ?? 0;
        $max = $values->max() ?? 0;

        return $rows->map(function ($row) use ($min, $max) {
            $row->intensity = ($max > $min)
                ? round((($row->value - $min) / ($max - $min)), 2)
                : ($row->value > 0 ? 1 : 0);

            return $row;
        });
    }

    protected function institutionCountByColumn(string $column)
    {
        return DB::table('institutions as i')
            ->whereNotNull("i.$column")
            ->when($this->level === 'district' && $this->division, fn ($q) => $q->where('i.division', $this->division))
            ->selectRaw("i.$column as label, COUNT(*) as value")
            ->groupBy("i.$column")
            ->orderByDesc('value')
            ->get();
    }

    protected function studentCountByColumn(string $column)
    {
        return DB::table('student_enrollments as se')
            ->join('institutions as i', 'i.id', '=', 'se.institution_id')
            ->where('se.status', 'running')
            ->whereNotNull("i.$column")
            ->when($this->level === 'district' && $this->division, fn ($q) => $q->where('i.division', $this->division))
            ->selectRaw("i.$column as label, COUNT(*) as value")
            ->groupBy("i.$column")
            ->orderByDesc('value')
            ->get();
    }

    protected function passRateByColumn(string $column)
    {
        return DB::table('exam_positions as ep')
            ->join('exam_setups as es', 'es.id', '=', 'ep.exam_setup_id')
            ->join('institutions as i', 'i.id', '=', 'ep.institution_id')
            ->whereNull('ep.deleted_at')
            ->where('es.is_result_published', true)
            ->whereNotNull("i.$column")
            ->when($this->academicSessionId, fn ($q) => $q->where('ep.academic_session_id', $this->academicSessionId))
            ->when($this->level === 'district' && $this->division, fn ($q) => $q->where('i.division', $this->division))
            ->selectRaw("
                i.$column as label,
                COUNT(*) as total,
                SUM(CASE WHEN ep.result = 'pass' THEN 1 ELSE 0 END) as pass_count
            ")
            ->groupBy("i.$column")
            ->get()
            ->map(function ($row) {
                $row->value = $row->total > 0 ? round($row->pass_count / $row->total * 100, 2) : 0.0;

                return $row;
            })
            ->sortByDesc('value')
            ->values();
    }

    public function render()
    {
        return view('livewire.ministry.geography.heatmap-component', [
            'divisions'        => $this->divisions,
            'academicSessions' => $this->academicSessions,
            'gridData'         => $this->gridData,
        ])
        ->layout('layouts.ministry.app', [
            'title' => 'Geography Heatmap | ' . setting('app_name', 'EMS'),
        ]);
    }
}

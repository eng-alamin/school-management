<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\Student;

class AttendanceComponent extends Component
{
    public string $filterMonth;

    /**
     * Kept for backward compatibility (in case other code/tests rely on it).
     */
    public array $data = [];

    /**
     * Calendar grid: array of weeks, each week is an array of 7 day-cells.
     */
    public array $calendarWeeks = [];

    public array $summary = [
        'present' => 0,
        'absent'  => 0,
        'late'    => 0,
        'leave'   => 0,
        'total'   => 0,
    ];

    /**
     * Overall attendance percentage for the selected month:
     * (present + late) / (present + absent + late) * 100.
     * Leave days are excluded from the base.
     */
    public float $attendancePercentage = 0;

    /**
     * CSS-friendly bucket for the ring color: 'high' | 'mid' | 'low'.
     */
    public string $ringLevel = 'high';

    public bool $hasAttendance = false;

    /**
     * Prevents navigating into future months.
     */
    public bool $isCurrentMonth = true;

    public function mount()
    {
        $this->filterMonth = now()->format('Y-m');
        $this->loadAttendance();
    }

    protected function currentStudent(): ?Student
    {
        $userId = auth()->id();

        if (!$userId) {
            return null;
        }

        return Student::where('user_id', $userId)->first();
    }

    public function updatedFilterMonth()
    {
        $this->loadAttendance();
    }

    public function previousMonth(): void
    {
        $this->filterMonth = Carbon::createFromFormat('Y-m', $this->filterMonth)
            ->subMonth()
            ->format('Y-m');

        $this->loadAttendance();
    }

    public function nextMonth(): void
    {
        // Do not allow navigating beyond the current month.
        if ($this->isCurrentMonth) {
            return;
        }

        $this->filterMonth = Carbon::createFromFormat('Y-m', $this->filterMonth)
            ->addMonth()
            ->format('Y-m');

        $this->loadAttendance();
    }

    public function loadAttendance()
    {
        $this->isCurrentMonth = $this->filterMonth === now()->format('Y-m');

        $student = $this->currentStudent();

        if (!$student) {
            $this->data                 = [];
            $this->calendarWeeks        = [];
            $this->hasAttendance        = false;
            $this->attendancePercentage = 0;
            $this->ringLevel            = 'high';
            $this->resetSummary();
            return;
        }

        $start = Carbon::createFromFormat('Y-m', $this->filterMonth)->startOfMonth()->format('Y-m-d');
        $end   = Carbon::createFromFormat('Y-m', $this->filterMonth)->endOfMonth()->format('Y-m-d');

        $records = Attendance::query()
            ->where('type', 'student')
            ->where('attendable_id', $student->id)
            ->where('attendable_type', Student::class)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $this->data = $records->map(function ($att) {
            return [
                'date'    => $att->date,
                'status'  => $att->status,
                'remarks' => $att->remarks,
            ];
        })->toArray();

        $this->buildSummary($records);
        $this->buildCalendar($records);
        $this->calculatePercentage();
        $this->hasAttendance = true;
    }

    protected function buildSummary($records): void
    {
        $this->resetSummary();

        foreach ($records as $record) {
            if (array_key_exists($record->status, $this->summary)) {
                $this->summary[$record->status]++;
            }
        }

        $this->summary['total'] = $records->count();
    }

    protected function resetSummary(): void
    {
        $this->summary = [
            'present' => 0,
            'absent'  => 0,
            'late'    => 0,
            'leave'   => 0,
            'total'   => 0,
        ];
    }

    protected function calculatePercentage(): void
    {
        $countedBase = $this->summary['present'] + $this->summary['absent'] + $this->summary['late'];

        $this->attendancePercentage = $countedBase > 0
            ? round((($this->summary['present'] + $this->summary['late']) / $countedBase) * 100, 1)
            : 0;

        $this->ringLevel = match (true) {
            $this->attendancePercentage >= 75 => 'high',
            $this->attendancePercentage >= 50 => 'mid',
            default => 'low',
        };
    }

    protected function buildCalendar($records): void
    {
        $recordsByDate = [];
        foreach ($records as $record) {
            $recordsByDate[Carbon::parse($record->date)->format('Y-m-d')] = [
                'status'  => $record->status,
                'remarks' => $record->remarks,
            ];
        }

        $monthStart = Carbon::createFromFormat('Y-m', $this->filterMonth)->startOfMonth();
        $monthEnd   = Carbon::createFromFormat('Y-m', $this->filterMonth)->endOfMonth();

        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $today = Carbon::today();

        $weeks  = [];
        $week   = [];
        $cursor = $gridStart->copy();

        while ($cursor->lte($gridEnd)) {
            $dateKey = $cursor->format('Y-m-d');
            $record  = $recordsByDate[$dateKey] ?? null;

            $week[] = [
                'date'      => $cursor->copy(),
                'inMonth'   => $cursor->month === $monthStart->month,
                'status'    => $record['status'] ?? null,
                'remarks'   => $record['remarks'] ?? null,
                'isToday'   => $cursor->isSameDay($today),
                'isFuture'  => $cursor->gt($today),
                'isWeekend' => in_array($cursor->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true),
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week    = [];
            }

            $cursor->addDay();
        }

        $this->calendarWeeks = $weeks;
    }

    public function render()
    {
        return view('livewire.student.attendance-component')
            ->layout('layouts.student.app', [
                'title' => 'My Attendance | ' . institution()->name,
            ]);
    }
}
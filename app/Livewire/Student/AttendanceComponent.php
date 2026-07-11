<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\Student;

class AttendanceComponent extends Component
{
    public string $filterMonth;

    public array $data = [];

    public array $summary = [
        'present' => 0,
        'absent'  => 0,
        'late'    => 0,
        'leave'   => 0,
        'total'   => 0,
    ];

    public bool $hasAttendance = false;

    public function mount()
    {
        $this->filterMonth = now()->format('Y-m');
        $this->loadAttendance();
    }

    /**
     * Resolve the logged-in student.
     *
     * The default 'web' guard is used. Auth::user() returns a User model,
     * and Student::user_id links back to that User, so we resolve the
     * Student record via that foreign key.
     */
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

    public function loadAttendance()
    {
        $student = $this->currentStudent();

        if (!$student) {
            $this->data          = [];
            $this->hasAttendance = false;
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

    public function render()
    {
        return view('livewire.student.attendance-component')
            ->layout('layouts.student.app', [
                'title' => 'My Attendance | ' . institution()->name,
            ]);
    }
}
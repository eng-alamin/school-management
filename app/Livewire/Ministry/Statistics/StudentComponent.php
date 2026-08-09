<?php

declare(strict_types=1);

namespace App\Livewire\Ministry\Statistics;

use App\Models\Institution;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class StudentComponent extends Component
{
    #[Url(history: true)]
    public string $division = '';

    public function resetFilters(): void
    {
        $this->division = '';
    }

    public function getDivisions(): array
    {
        return Institution::DIVISIONS;
    }

    /**
     * Common base query for all "current running" student enrollments.
     * Division filter thakle apply hobe.
     */
    private function baseQuery(): \Illuminate\Database\Query\Builder
    {
        $query = DB::table('student_enrollments as se')
            ->join('students as s', 'se.student_id', '=', 's.id')
            ->join('institutions as i', 'se.institution_id', '=', 'i.id')
            ->where('se.status', 'running');

        if ($this->division !== '') {
            $query->where('i.division', $this->division);
        }

        return $query;
    }

    private function totalStudents(): int
    {
        return $this->baseQuery()->count();
    }

    private function genderBreakdown(): Collection
    {
        return $this->baseQuery()
            ->select('s.gender', DB::raw('count(*) as total'))
            ->groupBy('s.gender')
            ->get();
    }

    private function classWiseBreakdown(): Collection
    {
        return $this->baseQuery()
            ->join('academic_classes as ac', 'se.class_id', '=', 'ac.id')
            ->select('ac.id', 'ac.name', 'ac.numeric', DB::raw('count(*) as total'))
            ->groupBy('ac.id', 'ac.name', 'ac.numeric')
            ->orderBy('ac.numeric')
            ->get();
    }

    private function divisionWiseBreakdown(): Collection
    {
        return $this->baseQuery()
            ->select('i.division', DB::raw('count(*) as total'))
            ->groupBy('i.division')
            ->orderByDesc('total')
            ->get();
    }

    public function render()
    {
        return view('livewire.ministry.statistics.student-component', [
            'divisions' => $this->getDivisions(),
            'totalStudents' => $this->totalStudents(),
            'genderBreakdown' => $this->genderBreakdown(),
            'classWiseBreakdown' => $this->classWiseBreakdown(),
            'divisionWiseBreakdown' => $this->divisionWiseBreakdown(),
        ])
        ->layout('layouts.ministry.app', [
            'title' => 'Student Statistics | ' . setting('app_name', 'EMS'),
        ]);
    }
}
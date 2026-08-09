<?php

namespace App\Livewire\Ministry;

use Livewire\Component;
use App\Models\Institution;
use App\Models\Student;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class DashboardComponent extends Component
{
    // ── Institutions ─────────────────────────────────────────────────────
    public int $totalInstitutions    = 0;
    public int $activeInstitutions   = 0;
    public int $inactiveInstitutions = 0;

    // ── Students & Teachers ─────────────────────────────────────────────
    public int $totalStudents  = 0;
    public int $totalTeachers  = 0;
    public int $activeTeachers = 0;

    // ── Recent Lists ─────────────────────────────────────────────────────
    public $recentInstitutions;
    public $recentActivities;

    public function mount(): void
    {
        // ── Institutions ─────────────────────────────────────────────────
        $this->totalInstitutions    = Institution::count();
        $this->activeInstitutions   = Institution::where('status', true)->count();
        $this->inactiveInstitutions = $this->totalInstitutions - $this->activeInstitutions;

        // ── Students ─────────────────────────────────────────────────────
        $this->totalStudents = Student::whereRelation('user', 'is_active', true)->count();

        // ── Teachers ─────────────────────────────────────────────────────
        $this->totalTeachers  = Employee::whereRelation('user', 'role', 'teacher')->count();
        $this->activeTeachers = Employee::whereRelation('user', 'role', 'teacher')
            ->whereRelation('user', 'is_active', true)
            ->count();

        // ── Recent Institutions ────────────────────────────────────────
        $this->recentInstitutions = Institution::orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'name', 'email', 'status', 'created_at')
            ->get();

        // ── Recent Activity ────────────────────────────────────────────
        $this->recentActivities = DB::table('activity_log')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'description', 'properties', 'created_at')
            ->get()
            ->map(function ($act) {
                $props     = json_decode($act->properties, true);
                $act->icon = $props['icon'] ?? 'notifications';
                return $act;
            });
    }

    public function render()
    {
        return view('livewire.ministry.dashboard-component')
            ->layout('layouts.ministry.app', [
                'title' => 'Dashboard | ' . setting('app_name', 'EMS'),
            ]);
    }
}
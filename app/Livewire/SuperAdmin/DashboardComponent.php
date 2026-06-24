<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\Institution;
use App\Models\User;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardComponent extends Component
{
    // ── Institutions ────────────────────────────────────────────────────────────
    public int   $totalInstitutions       = 0;
    public int   $activeInstitutions      = 0;
    public int   $inactiveInstitutions    = 0;

    // ── Students & Teachers ────────────────────────────────────────────────
    public int   $totalStudents      = 0;
    public int   $totalTeachers      = 0;
    public int   $activeTeachers     = 0;

    // ── Revenue / Billing ──────────────────────────────────────────────────
    public float $totalRevenue       = 0;
    public float $revenueThisMonth   = 0;
    public int   $pendingInvoices    = 0;
    public float $pendingAmount      = 0;

    // ── Recent Lists ───────────────────────────────────────────────────────
    public $recentInstitutions;
    public $recentInvoices;
    public $recentActivities;

    public function mount(): void
    {
        // ── Institutions ────────────────────────────────────────────────────────
        $this->totalInstitutions    = Institution::count();
        $this->activeInstitutions   = Institution::where('status', true)->count();
        $this->inactiveInstitutions = $this->totalInstitutions - $this->activeInstitutions;

        // ── Students ───────────────────────────────────────────────────────
        $this->totalStudents = Student::whereRelation('user', 'is_active', true)->count();

        // ── Teachers ───────────────────────────────────────────────────────
        $this->totalTeachers = Employee::whereRelation('user', 'role', 'teacher')->count();
        $this->activeTeachers = Employee::whereRelation('user', 'role', 'teacher')->whereRelation('user', 'is_active', true)->count();

        // ── Revenue ────────────────────────────────────────────────────────
        // type = 'billing' মানে institution subscription invoice
        $this->totalRevenue = (float) Invoice::where('type', 'billing')
            ->where('status', 'paid')
            ->sum('payable_amount');

        $this->revenueThisMonth = (float) Invoice::where('type', 'billing')
            ->where('status', 'paid')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('payable_amount');

        // ── Pending Invoices ───────────────────────────────────────────────
        $this->pendingInvoices = Invoice::where('type', 'billing')
            ->where('status', 'pending')
            ->count();

        $this->pendingAmount = (float) Invoice::where('type', 'billing')
            ->where('status', 'pending')
            ->sum('payable_amount');

        // ── Recent Institutions ─────────────────────────────────────────────────
        $this->recentInstitutions = Institution::orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'name', 'email', 'status', 'created_at')
            ->get();

        // ── Recent Billing Invoices ────────────────────────────────────────
        $this->recentInvoices = Invoice::with('institution:id,name')
            ->where('type', 'billing')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'institution_id', 'invoice_no', 'payable_amount', 'status', 'due_date')
            ->get();

        // ── Recent Activities ──────────────────────────────────────────────
        $this->recentActivities = DB::table('activity_log')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'description', 'properties', 'created_at')
            ->get()
            ->map(function ($act) {
                $props      = json_decode($act->properties, true);
                $act->icon  = $props['icon'] ?? 'notifications';
                return $act;
            });
    }

    public function render()
    {
        return view('livewire.super-admin.dashboard-component')
            ->layout('layouts.superadmin.app', [
                'title' => 'Dashboard | ' . setting('app_name', 'EMS'),
            ]);
    }
}
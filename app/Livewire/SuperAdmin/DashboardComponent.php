<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardComponent extends Component
{
    // ── Schools ────────────────────────────────────────────────────────────
    public int   $totalSchools       = 0;
    public int   $activeSchools      = 0;
    public int   $inactiveSchools    = 0;

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
    public $recentSchools;
    public $recentInvoices;
    public $recentActivities;

    public function mount(): void
    {
        // ── Schools ────────────────────────────────────────────────────────
        $this->totalSchools    = School::count();
        $this->activeSchools   = School::where('status', true)->count();
        $this->inactiveSchools = $this->totalSchools - $this->activeSchools;

        // ── Students ───────────────────────────────────────────────────────
        $this->totalStudents = Student::whereRelation('user', 'is_active', true)->count();

        // ── Teachers ───────────────────────────────────────────────────────
        $this->totalTeachers = Employee::whereRelation('user', 'role', 'teacher')->count();
        $this->activeTeachers = Employee::whereRelation('user', 'role', 'teacher')->whereRelation('user', 'is_active', true)->count();

        // ── Revenue ────────────────────────────────────────────────────────
        // type = 'billing' মানে school subscription invoice
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

        // ── Recent Schools ─────────────────────────────────────────────────
        $this->recentSchools = School::orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'name', 'email', 'status', 'created_at')
            ->get();

        // ── Recent Billing Invoices ────────────────────────────────────────
        $this->recentInvoices = Invoice::with('school:id,name')
            ->where('type', 'billing')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('id', 'school_id', 'invoice_no', 'payable_amount', 'status', 'due_date')
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
                'title' => 'Super Admin Dashboard',
            ]);
    }
}
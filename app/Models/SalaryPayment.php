<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPayment extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'month'           => 'date',
        'payment_date'    => 'date',
        'basic_salary'    => 'float',
        'total_allowance' => 'float',
        'total_deduction' => 'float',
        'overtime_hour'   => 'float',
        'overtime_rate'   => 'float',
        'overtime_amount' => 'float',
        'gross_salary'    => 'float',
        'net_salary'      => 'float',
    ];

    // ── Who this payment belongs to ───────────────────────────────
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // ── The salary assign snapshot used for this payment ──────────
    public function salaryAssign()
    {
        return $this->belongsTo(SalaryAssign::class, 'salary_assign_id');
    }

    // ── Office account debited ─────────────────────────────────────
    public function account()
    {
        return $this->belongsTo(OfficeAccount::class, 'account_id');
    }

    // ── Admin/user who processed the payment ──────────────────────
    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeForMonth($query, string $month)
    {
        // $month format: Y-m  e.g. "2026-05"
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return $query->whereYear('month', $date->year)
                     ->whereMonth('month', $date->month);
    }
}
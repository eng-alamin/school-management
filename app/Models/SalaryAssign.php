<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryAssign extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'basic_salary'    => 'decimal:2',
        'overtime_rate'   => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'gross_salary'    => 'decimal:2',
        'net_salary'      => 'decimal:2',
    ];

    // ── Employee ─────────────────────────────────────────────────
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // ── Salary Template ──────────────────────────────────────────
    public function salaryTemplate()
    {
        return $this->belongsTo(SalaryTemplate::class);
    }

    // ── Designation ──────────────────────────────────────────────
    public function designation()
    {
        return $this->belongsTo(EmployeeDesignation::class, 'designation_id');
    }

    // ── Payments ─────────────────────────────────────────────────
    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    // ── Scopes ───────────────────────────────────────────────────
    public function scopeForRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
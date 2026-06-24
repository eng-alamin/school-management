<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class SalaryAssign extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];
 
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
 
    // ── Payments ─────────────────────────────────────────────────
    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}

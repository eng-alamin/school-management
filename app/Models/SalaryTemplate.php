<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class SalaryTemplate extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function allowances()
    {
        return $this->hasMany(SalaryTemplateAllowance::class);
    }

    public function deductions()
    {
        return $this->hasMany(SalaryTemplateDeduction::class);
    }
}

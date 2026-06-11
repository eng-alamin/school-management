<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class SalaryTemplateAllowance extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function salaryTemplate()
    {
        return $this->belongsTo(SalaryTemplate::class);
    }
}

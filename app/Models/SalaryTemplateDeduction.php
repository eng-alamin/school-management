<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class SalaryTemplateDeduction extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function salaryTemplate()
    {
        return $this->belongsTo(SalaryTemplate::class);
    }
}

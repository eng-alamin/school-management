<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToInstitution;

class SalaryAdvanceRepayment extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'amount'        => 'decimal:2',
        'deducted_date' => 'date',
    ];

    public function salaryAdvance(): BelongsTo
    {
        return $this->belongsTo(SalaryAdvance::class);
    }

    public function salaryPayment(): BelongsTo
    {
        return $this->belongsTo(SalaryPayment::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdvanceRepayment extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;

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
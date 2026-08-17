<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFine extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'amount'    => 'decimal:2',
        'fine_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function feeInvoice()
    {
        return $this->belongsTo(FeeInvoice::class);
    }
}
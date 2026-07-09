<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

class StudentFine extends Model
{
    use BelongsToInstitution;

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
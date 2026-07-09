<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Admission extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'dob'        => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function appliedSession()
    {
        return $this->belongsTo(AcademicSession::class, 'applied_session_id');
    }

    public function appliedClass()
    {
        return $this->belongsTo(AcademicClass::class, 'applied_class_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function invoice()
    {
        return $this->hasOne(FeeInvoice::class, 'admission_id');
    }
}
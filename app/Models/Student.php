<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot('institution_id')
            ->withTimestamps();
    }

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function group()
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function feeAllocations()
    {
        return $this->hasMany(FeeAllocation::class, 'student_id');
    }

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function sales()
    {
        return $this->morphMany(Sale::class, 'saleable');
    }

    public function leaveApplications()
    {
        return $this->morphMany(LeaveApplication::class, 'applicable');
    }
}
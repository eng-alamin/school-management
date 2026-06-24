<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Student extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];
    
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

    // public function guardians()
    // {
    //     return $this->belongsToMany(
    //         Guardian::class,
    //         'guardian_student',
    //         'student_id',
    //         'guardian_id'
    //     );
    // }

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function category()
    {
        return $this->belongsTo(AcademicCategory::class, 'category_id');
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
        return $this->hasMany(FeeInvoice::class, 'student_id');
    }
    
    public function sales()
    {
        return $this->morphMany(Sale::class, 'saleable');
    }

    public function leaveApplications()
    {
        return $this->morphMany(LeaveApplication::class,'applicable');
    }
}

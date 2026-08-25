<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

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

    // pore class() remove korbo
    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }
    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    // pore section() remove korbo
    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }
    public function academicSection()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    // pore group() remove korbo
    public function group()
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }
    public function academicGroup()
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function sales()
    {
        return $this->morphMany(InventorySale::class, 'saleable');
    }

    public function leaveApplications()
    {
        return $this->morphMany(LeaveApplication::class, 'applicable');
    }

    public function biometricMappings()
    {
        return $this->morphMany(BiometricDeviceUserMapping::class, 'attendable');
    }
}
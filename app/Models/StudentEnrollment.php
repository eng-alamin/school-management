<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEnrollment extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;
    
    protected $fillable = [
        'institution_id',
        'branch_id',
        'student_id',
        'class_id',
        'section_id',
        'group_id',
        'roll_no',
        'status',
        'carry_forward_due',
    ];
 
    protected $casts = [
        'carry_forward_due' => 'boolean',
    ];
 
    public function student()
    {
        return $this->belongsTo(Student::class);
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
}

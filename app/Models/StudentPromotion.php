<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class StudentPromotion extends Model
{
    use BelongsToInstitution;
    
    protected $fillable = [
        'institution_id',
        'student_id',
        'from_session_id',
        'to_session_id',
        'from_class_id',
        'to_class_id',
        'from_section_id',
        'to_section_id',
        'from_group_id',
        'to_group_id',
        'from_roll_no',
        'to_roll_no',
        'carry_forward_due',
        'is_alumni',
        'promoted_by',
        'promoted_at',
    ];
 
    protected $casts = [
        'carry_forward_due' => 'boolean',
        'is_alumni'         => 'boolean',
        'promoted_at'       => 'datetime',
    ];
 
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
 
    public function fromSession()
    {
        return $this->belongsTo(AcademicSession::class, 'from_session_id');
    }
 
    public function toSession()
    {
        return $this->belongsTo(AcademicSession::class, 'to_session_id');
    }
 
    public function fromClass()
    {
        return $this->belongsTo(AcademicClass::class, 'from_class_id');
    }
 
    public function toClass()
    {
        return $this->belongsTo(AcademicClass::class, 'to_class_id');
    }
 
    public function fromSection()
    {
        return $this->belongsTo(AcademicSection::class, 'from_section_id');
    }
 
    public function toSection()
    {
        return $this->belongsTo(AcademicSection::class, 'to_section_id');
    }
 
    public function fromGroup()
    {
        return $this->belongsTo(AcademicGroup::class, 'from_group_id');
    }
 
    public function toGroup()
    {
        return $this->belongsTo(AcademicGroup::class, 'to_group_id');
    }
 
    public function promotedBy()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}

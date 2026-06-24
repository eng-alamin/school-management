<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class FeeAllocation extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function feeGroup()
    {
        return $this->belongsTo(FeeGroup::class, 'fee_group_id');
    }

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function invoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }
}

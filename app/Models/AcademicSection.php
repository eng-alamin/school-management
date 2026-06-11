<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class AcademicSection extends Model
{
    use BelongsToSchool;
    
    protected $guarded = [];

    public function classes()
    {
        return $this->belongsToMany(
            AcademicClass::class,
            'academic_class_sections',
            'section_id',
            'class_id'
        );
    }

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function feeAllocations()
    {
        return $this->hasMany(FeeAllocation::class);
    }

    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class);
    }
}

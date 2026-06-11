<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class AcademicClass extends Model
{
    use BelongsToSchool;
    
    protected $guarded = [];

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function sections()
    {
        return $this->belongsToMany(
            AcademicSection::class,
            'academic_class_sections',
            'class_id',
            'section_id'
        );
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

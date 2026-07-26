<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicSection extends Model
{
    use BelongsToInstitution;
    use SoftDeletes;
    
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

    // Remove 
    // public function feeInvoices()
    // {
    //     return $this->hasMany(FeeInvoice::class);
    // }
}

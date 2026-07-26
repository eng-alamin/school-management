<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicClass extends Model
{
    use BelongsToInstitution;
    use SoftDeletes;
    
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


    // Remove 
    // public function feeInvoices()
    // {
    //     return $this->hasMany(FeeInvoice::class);
    // }

}

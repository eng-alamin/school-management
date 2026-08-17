<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeSetup extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;
 
    protected $guarded = [];
 
    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'boolean',
    ];
 
    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }
 
    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamPosition extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;
    
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function examSetup()
    {
        return $this->belongsTo(ExamSetup::class);
    }

}

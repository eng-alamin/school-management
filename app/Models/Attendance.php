<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;
    
    protected $guarded = [];
    
    public function attendable()
    {
        return $this->morphTo();
    }

    public function exam()
    {
        return $this->belongsTo(ExamSetup::class, 'exam_id');
    }

    public function subject()
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }

}

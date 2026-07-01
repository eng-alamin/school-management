<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class ExamPosition extends Model
{
    use BelongsToInstitution;
    
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

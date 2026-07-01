<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class ExamEntry extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'is_absent'          => 'boolean',
        'practical_obtained' => 'float',
        'written_obtained'   => 'float',
        'mcq_obtained'       => 'float',
        'total_obtained'     => 'float',
    ];

    public function examSetup()
    {
        return $this->belongsTo(ExamSetup::class, 'exam_setup_id');
    }

    public function examSetupDetail()
    {
        return $this->belongsTo(ExamSetupDetail::class, 'exam_setup_detail_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
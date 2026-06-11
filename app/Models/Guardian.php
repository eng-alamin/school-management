<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Guardian extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
                    ->withPivot('school_id')
                    ->withTimestamps();
    }

    // public function students()
    // {
    //     return $this->belongsToMany(
    //         Student::class,
    //         'guardian_student',
    //         'guardian_id',
    //         'student_id'
    //     );
    // }

    public function sales()
    {
        return $this->morphMany(Sale::class, 'saleable');
    }
}

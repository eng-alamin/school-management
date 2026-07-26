<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use BelongsToInstitution;
    use SoftDeletes;

    protected $guarded = [];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot('institution_id')
            ->withTimestamps();
    }

    public function sales()
    {
        return $this->morphMany(InventorySale::class, 'saleable');
    }
}
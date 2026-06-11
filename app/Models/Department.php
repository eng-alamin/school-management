<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Department extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Department extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}

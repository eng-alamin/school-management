<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDesignation extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

}

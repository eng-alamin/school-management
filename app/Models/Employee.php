<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Employee extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(EmployeeDepartment::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(EmployeeDesignation::class, 'designation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function sales()
    {
        return $this->morphMany(Sale::class, 'saleable');
    }

    public function leaveApplications()
    {
        return $this->morphMany(LeaveApplication::class, 'applicable');
    }
}
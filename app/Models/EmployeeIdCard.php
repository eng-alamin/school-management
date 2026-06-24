<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeIdCard extends Model
{
    use BelongsToInstitution;
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'template_id', 'employee_id', 'name', 'designation', 'department',
        'organization_name', 'organization_address', 'blood_group', 'date_of_birth',
        'contact_number', 'email', 'address', 'photo', 'signature',
        'joining_date', 'expiry_date', 'emergency_contact', 'status',
    ];
 
    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'expiry_date' => 'date',
    ];
 
    public function template()
    {
        return $this->belongsTo(IdCardTemplate::class, 'template_id');
    }
 
    public static function getBloodGroups(): array
    {
        return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    }
 
    public static function getStatuses(): array
    {
        return ['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'];
    }
 
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(storage_path('app/public/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }
        return asset('images/default-avatar.png');
    }
}

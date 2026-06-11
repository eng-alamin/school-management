<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmitCard extends Model
{
    use BelongsToSchool;
    use HasFactory, SoftDeletes;
 
    protected $guarded = [];
 
    protected $casts = [
        'date_of_birth' => 'date',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'exam_schedules' => 'array',
    ];
 
    public function template()
    {
        return $this->belongsTo(AdmitCardTemplate::class, 'template_id');
    }
 
    public static function getBloodGroups(): array
    {
        return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    }
 
    public static function getStatuses(): array
    {
        return ['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired'];
    }
 
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(storage_path('app/public/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }
        return asset('images/default-avatar.png');
    }
}

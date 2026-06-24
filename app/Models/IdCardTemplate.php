<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IdCardTemplate extends Model
{
    use BelongsToInstitution;
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'name', 'type', 'background_color', 'text_color', 'accent_color',
        'logo_path', 'background_image', 'header_text', 'footer_text',
        'card_width', 'card_height', 'fields', 'show_photo',
        'show_barcode', 'show_qrcode', 'is_active',
    ];
 
    protected $casts = [
        'fields' => 'array',
        'show_photo' => 'boolean',
        'show_barcode' => 'boolean',
        'show_qrcode' => 'boolean',
        'is_active' => 'boolean',
    ];
 
    public function studentCards()
    {
        return $this->hasMany(StudentIdCard::class, 'template_id');
    }
 
    public function employeeCards()
    {
        return $this->hasMany(EmployeeIdCard::class, 'template_id');
    }
 
    public static function getTypes(): array
    {
        return ['general' => 'General', 'student' => 'Student', 'employee' => 'Employee'];
    }
}

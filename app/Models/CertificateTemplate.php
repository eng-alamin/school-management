<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateTemplate extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active'    => 'boolean',
        'photo_size'   => 'integer',
        'margin_top'   => 'integer',
        'margin_right' => 'integer',
        'margin_bottom'=> 'integer',
        'margin_left'  => 'integer',
    ];

    // Page layout dimensions in mm
    public function getPageDimensionsAttribute(): array
    {
        return match($this->page_layout) {
            'a4_portrait'  => ['width' => 210, 'height' => 297],
            'a4_landscape' => ['width' => 297, 'height' => 210],
            'a5_portrait'  => ['width' => 148, 'height' => 210],
            'a5_landscape' => ['width' => 210, 'height' => 148],
            default        => ['width' => 210, 'height' => 297],
        };
    }

    // Replace placeholders with actual student/employee data
    public function renderContent(array $data): string
    {
        $content = $this->certificate_content;

        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value ?? '', $content);
        }

        return $content;
    }

    // Available placeholders based on applicable_user
    public function getAvailablePlaceholdersAttribute(): array
    {
        $common = [
            '{institute_name}', '{institute_email}', '{institute_mobile}',
            '{institute_address}','{logo}', '{signature}', '{qr_code}', '{print_date}',
        ];

        $student = [
            '{name}', '{gender}', '{father_name}', '{mother_name}',
            '{student_photo}', '{register_no}', '{roll_no}', '{admission_date}',
            '{class}', '{section}', '{category}', '{caste}', '{religion}',
            '{blood_group}', '{birthday}', '{email}', '{mobileno}',
            '{present_address}', '{permanent_address}',
        ];

        $employee = [
            '{name}', '{gender}', '{email}', '{mobile}',
            '{employee_photo}', '{employee_id}', '{designation}',
            '{department}', '{joining_date}', '{blood_group}',
            '{present_address}', '{permanent_address}',
        ];

        return match($this->applicable_user) {
            'student'  => array_merge($common, $student),
            'employee' => array_merge($common, $employee),
            default    => $common,
        };
    }

    // Scope for active templates
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope filter by user type
    public function scopeForUser($query, string $type)
    {
        return $query->where('applicable_user', $type);
    }

}

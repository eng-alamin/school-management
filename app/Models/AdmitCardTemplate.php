<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmitCardTemplate extends Model
{
    use BelongsToSchool;
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'name', 'exam_type', 'background_color', 'text_color', 'accent_color',
        'logo_path', 'header_text', 'instructions', 'footer_text',
        'show_photo', 'show_signature', 'show_barcode', 'is_active',
    ];
 
    protected $casts = [
        'show_photo' => 'boolean',
        'show_signature' => 'boolean',
        'show_barcode' => 'boolean',
        'is_active' => 'boolean',
    ];
 
    public function admitCards()
    {
        return $this->hasMany(AdmitCard::class, 'template_id');
    }
 
    public static function getExamTypes(): array
    {
        return [
            'annual' => 'Annual Exam',
            'half-yearly' => 'Half Yearly Exam',
            'quarterly' => 'Quarterly Exam',
            'monthly' => 'Monthly Exam',
            'entrance' => 'Entrance Exam',
            'board' => 'Board Exam',
            'other' => 'Other',
        ];
    }
}

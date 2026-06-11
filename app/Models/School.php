<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class School extends Model
{
    use BelongsToSchool;
    
    protected $guarded = [];

    protected $casts = [
        'weekends'                       => 'array',
        'teacher_restricted'             => 'boolean',
        'enable_registration_prefix'     => 'boolean',
        'offline_payment_enabled'        => 'boolean',
        'due_fees_calculation_with_fine' => 'boolean',
        'auto_generate_student_login'    => 'boolean',
        'auto_generate_guardian_login'   => 'boolean',
        'status'                         => 'boolean',
    ];

    // ৳15,000 format
    public function formatCurrency(float $amount): string
    {
        $formatted = number_format($amount);

        return $this->symbol_position === 'prefix'
            ? $this->currency_symbol . $formatted
            : $formatted . $this->currency_symbol;
    }

    // Friday → true/false
    public function isWeekend(string $day): bool
    {
        return in_array($day, $this->weekends ?? []);
    }

    // Registration number generate
    public function generateRegNo(int $lastNumber): string
    {
        $number = str_pad($lastNumber + 1, $this->register_no_digit, '0', STR_PAD_LEFT);

        return $this->enable_registration_prefix
            ? $this->institution_code_prefix . $number
            : $number;
    }
}

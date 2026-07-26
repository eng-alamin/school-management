<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    protected $casts = [
        'weekends'                       => 'array',
        'enable_registration_prefix'     => 'boolean',
        'due_fees_calculation_with_fine' => 'boolean',
        'status'                         => 'boolean',
        'facilities'                     => 'array',
    ];

    // Friday → true/false
    public function isWeekend(string $day): bool
    {
        return in_array($day, $this->weekends ?? []);
    }

    // Registration number generate
    public function generateRegNo(int $lastNumber): string
    {
        $number = str_pad(
            (string) ($lastNumber + 1),
            $this->registration_digit_length,
            '0',
            STR_PAD_LEFT
        );
 
        return $this->enable_registration_prefix
            ? $this->registration_code_prefix . $number
            : $number;
    }

    public function admin()
    {
        return $this->hasOne(User::class, 'institution_id')->where('role', 'admin');
    }

   public function currentSession()
    {
        return $this->hasOne(AcademicSession::class)->where('is_current', true);
    }

}

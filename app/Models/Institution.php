<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'setup_progress'                 => 'array',
        'setup_completed'                => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::created(function (Institution $institution) {
            // Every institution must always have at least one branch, even if
            // the school never uses the multi-branch feature. This keeps
            // branch_id non-null on every downstream record (student, staff,
            // fee, attendance, exam...) so no module ever needs a NULL-branch
            // special case.
            $institution->branches()->create([
                'name'      => 'Main Branch',
                'code'      => 'MAIN',
                'is_main'   => true,
                'is_active' => true,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Medium Constants
    |--------------------------------------------------------------------------
    */
    public const MEDIUM_BANGLA = 'bangla_medium';
    public const MEDIUM_BANGLA_ENGLISH_VERSION = 'bangla_medium_english_version';
    public const MEDIUM_ENGLISH = 'english_medium';

    public const MEDIUMS = [
        self::MEDIUM_BANGLA,
        self::MEDIUM_BANGLA_ENGLISH_VERSION,
        self::MEDIUM_ENGLISH,
    ];

    public const MEDIUM_LABELS = [
        self::MEDIUM_BANGLA                 => 'Bangla Medium',
        self::MEDIUM_BANGLA_ENGLISH_VERSION => 'Bangla Medium & English Version',
        self::MEDIUM_ENGLISH                => 'English Medium',
    ];

    /*
    |--------------------------------------------------------------------------
    | Medium Helpers
    |--------------------------------------------------------------------------
    */
    public function mediumLabel(): string
    {
        return self::MEDIUM_LABELS[$this->medium] ?? $this->medium;
    }

    public const DIVISIONS = [
        'dhaka'      => 'Dhaka',
        'chattogram' => 'Chattogram',
        'rajshahi'   => 'Rajshahi',
        'khulna'     => 'Khulna',
        'barishal'   => 'Barishal',
        'sylhet'     => 'Sylhet',
        'rangpur'    => 'Rangpur',
        'mymensingh' => 'Mymensingh',
    ];

    /*
    |--------------------------------------------------------------------------
    | Setup Wizard Steps
    |--------------------------------------------------------------------------
    */
    public const STEP_EMPLOYEE            = 'employee';
    public const STEP_SESSION             = 'session';
    public const STEP_CLASS_SETUP         = 'class_setup';
    public const STEP_CLASS_ASSIGN        = 'class_assign';
    public const STEP_CLASS_SCHEDULE      = 'class_schedule';
    public const STEP_FEE_SETUP           = 'fee_setup';
    public const STEP_STUDENT             = 'student';
    public const STEP_PARENT              = 'parent';

    public const SETUP_STEPS = [
        self::STEP_EMPLOYEE,
        self::STEP_SESSION,
        self::STEP_CLASS_SETUP,
        self::STEP_CLASS_ASSIGN,
        self::STEP_CLASS_SCHEDULE,
        self::STEP_FEE_SETUP,
        self::STEP_STUDENT,
        self::STEP_PARENT,
    ];

    /*
    |--------------------------------------------------------------------------
    | Setup Wizard Helpers
    |--------------------------------------------------------------------------
    */
    public function isStepCompleted(string $step): bool
    {
        return (bool) ($this->setup_progress[$step] ?? false);
    }
    public function markStepComplete(string $step): void
    {
        if (!in_array($step, self::SETUP_STEPS, true)) {
            return;
        }

        $progress = $this->setup_progress ?? [];
        $progress[$step] = true;

        $this->setup_progress = $progress;

        $allDone = count(array_intersect_key(
            array_filter($progress),
            array_flip(self::SETUP_STEPS)
        )) === count(self::SETUP_STEPS);

        if ($allDone) {
            $this->setup_completed = true;
        }

        $this->save();
    }
    public function markStepIncomplete(string $step): void
    {
        $progress = $this->setup_progress ?? [];
        $progress[$step] = false;

        $this->setup_progress = $progress;
        $this->setup_completed = false;
        $this->save();
    }
    public function setupProgressPercent(): int
    {
        $progress = $this->setup_progress ?? [];
        $done = count(array_filter(
            array_intersect_key($progress, array_flip(self::SETUP_STEPS))
        ));

        return (int) round(($done / count(self::SETUP_STEPS)) * 100);
    }
    public function skipSetupWizard(): void
    {
        $this->setup_completed = true;
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Helpers
    |--------------------------------------------------------------------------
    */

    public function isWeekend(string $day): bool
    {
        return in_array($day, $this->weekends ?? []);
    }

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

    /*
    |--------------------------------------------------------------------------
    | Branch Relations
    |--------------------------------------------------------------------------
    */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function mainBranch()
    {
        return $this->hasOne(Branch::class)->where('is_main', true);
    }
}
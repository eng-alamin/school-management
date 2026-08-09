<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'verified_at'                    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::created(function (Institution $institution) {
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

    public function mediumLabel(): string
    {
        return self::MEDIUM_LABELS[$this->medium] ?? $this->medium;
    }

    /*
    |--------------------------------------------------------------------------
    | Division / District Constants
    |--------------------------------------------------------------------------
    */
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

    /**
     * Division => Districts (English) mapping.
     * Keys MUST exactly match self::DIVISIONS keys.
     */
    public const DISTRICTS = [
        'dhaka' => [
            'Dhaka', 'Faridpur', 'Gazipur', 'Gopalganj', 'Kishoreganj',
            'Madaripur', 'Manikganj', 'Munshiganj', 'Narayanganj',
            'Narsingdi', 'Rajbari', 'Shariatpur', 'Tangail',
        ],
        'chattogram' => [
            'Chattogram', 'Cumilla', 'Brahmanbaria', 'Chandpur', 'Noakhali',
            'Feni', 'Lakshmipur', "Cox's Bazar", 'Khagrachhari', 'Rangamati', 'Bandarban',
        ],
        'rajshahi' => [
            'Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Natore',
            'Naogaon', 'Chapainawabganj', 'Joypurhat',
        ],
        'khulna' => [
            'Khulna', 'Jashore', 'Satkhira', 'Bagerhat', 'Jhenaidah',
            'Magura', 'Narail', 'Kushtia', 'Chuadanga', 'Meherpur',
        ],
        'barishal' => [
            'Barishal', 'Bhola', 'Jhalokathi', 'Patuakhali', 'Pirojpur', 'Barguna',
        ],
        'sylhet' => [
            'Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj',
        ],
        'rangpur' => [
            'Rangpur', 'Dinajpur', 'Kurigram', 'Gaibandha', 'Lalmonirhat',
            'Nilphamari', 'Panchagarh', 'Thakurgaon',
        ],
        'mymensingh' => [
            'Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur',
        ],
    ];

    /**
     * Districts belonging to a given division key.
     * Usage: Institution::districtsFor('dhaka')
     */
    public static function districtsFor(?string $divisionKey): array
    {
        return self::DISTRICTS[$divisionKey] ?? [];
    }

    /**
     * Districts belonging to this institution's own division.
     * Usage: $institution->availableDistricts()
     */
    public function availableDistricts(): array
    {
        return self::districtsFor($this->division);
    }

    public function divisionLabel(): string
    {
        return self::DIVISIONS[$this->division] ?? $this->division;
    }

    /*
    |--------------------------------------------------------------------------
    | Verification Constants (Ministry Oversight)
    |--------------------------------------------------------------------------
    */
    public const VERIFICATION_PENDING   = 'pending';
    public const VERIFICATION_VERIFIED  = 'verified';
    public const VERIFICATION_REJECTED  = 'rejected';
    public const VERIFICATION_SUSPENDED = 'suspended';

    public const VERIFICATION_STATUSES = [
        self::VERIFICATION_PENDING,
        self::VERIFICATION_VERIFIED,
        self::VERIFICATION_REJECTED,
        self::VERIFICATION_SUSPENDED,
    ];

    public const VERIFICATION_LABELS = [
        self::VERIFICATION_PENDING   => 'Pending',
        self::VERIFICATION_VERIFIED  => 'Verified',
        self::VERIFICATION_REJECTED  => 'Rejected',
        self::VERIFICATION_SUSPENDED => 'Suspended',
    ];

    public function verificationLabel(): string
    {
        return self::VERIFICATION_LABELS[$this->verification_status] ?? $this->verification_status;
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Setup Wizard Steps
    |--------------------------------------------------------------------------
    */
    public const STEP_EMPLOYEE       = 'employee';
    public const STEP_SESSION        = 'session';
    public const STEP_CLASS_SETUP    = 'class_setup';
    public const STEP_CLASS_ASSIGN   = 'class_assign';
    public const STEP_CLASS_SCHEDULE = 'class_schedule';
    public const STEP_FEE_SETUP      = 'fee_setup';
    public const STEP_STUDENT        = 'student';
    public const STEP_PARENT         = 'parent';

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

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function mainBranch()
    {
        return $this->hasOne(Branch::class)->where('is_main', true);
    }
}
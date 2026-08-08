<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    // Role constants — magic string এর বদলে constant ব্যবহার করো
    const ROLE_ADMIN       = 'admin';
    const ROLE_TEACHER     = 'teacher';
    const ROLE_STAFF       = 'staff';
    const ROLE_ACCOUNTANT  = 'accountant';
    const ROLE_STUDENT     = 'student';
    const ROLE_PARENT      = 'parent';
    const ROLE_MINISTRY    = 'ministry';
    const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'institution_id',
        'role',
        'name',
        'username',
        'phone',
        'email',
        'email_verified_at',
        'password',
        'avatar',
        'is_verified',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_login_at'     => 'datetime',
            'is_active'         => 'boolean',
            'is_verified'       => 'boolean',
        ];
    }

    // =====================
    // Relationships
    // =====================

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class, 'user_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    // =====================
    // Role Helpers
    // =====================

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isAccountant(): bool
    {
        return $this->role === self::ROLE_ACCOUNTANT;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    public function isMinistry(): bool
    {
        return $this->role === self::ROLE_MINISTRY;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    // =====================
    // Profile Helper
    // =====================

    /**
     * Role অনুযায়ী profile return করে
     * null আসলে এখান থেকেই আসে!
     */
    public function profile()
    {
        return match($this->role) {
            self::ROLE_ADMIN      => $this->employee,
            self::ROLE_TEACHER    => $this->employee,
            self::ROLE_STAFF      => $this->employee,
            self::ROLE_ACCOUNTANT => $this->employee,
            self::ROLE_STUDENT    => $this->student,
            self::ROLE_PARENT     => $this->guardian,
            default               => null,
        };
    }

    // =====================
    // Dashboard Redirect Helper
    // =====================

    /**
     * Role অনুযায়ী dashboard route name return করে।
     * routes/web.php এবং LoginComponent দুই জায়গাতেই reuse করা যাবে।
     *
     * @throws \RuntimeException যদি role অচেনা/অজানা হয়
     */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN       => 'admin.dashboard',
            self::ROLE_TEACHER     => 'teacher.dashboard',
            self::ROLE_STUDENT     => 'student.dashboard',
            self::ROLE_PARENT      => 'parent.dashboard',
            self::ROLE_ACCOUNTANT  => 'accountant.dashboard',
            self::ROLE_MINISTRY    => 'ministry.dashboard',
            self::ROLE_SUPER_ADMIN => 'superadmin.dashboard',
            default => throw new \RuntimeException("Unknown user role for dashboard redirect: {$this->role}"),
        };
    }

    // ─── Notification Relationships ───────────────────────────────────────────────
    public function notifications(): MorphMany
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable')
            ->latest();
    }
    public function unreadNotifications(): MorphMany
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable')
            ->whereNull('read_at')
            ->latest();
    }
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }
    public function markAllNotificationsAsRead(): void
    {
        $this->unreadNotifications()->update(['read_at' => now()]);
    }
}
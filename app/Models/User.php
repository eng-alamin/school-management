<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Role constants — magic string এর বদলে constant ব্যবহার করো
    const ROLE_ADMIN    = 'admin';
    const ROLE_TEACHER  = 'teacher';
    const ROLE_STAFF    = 'staff';
    const ROLE_ACCOUNTANT    = 'accountant';
    const ROLE_STUDENT  = 'student';
    const ROLE_PARENT   = 'parent';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

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
            self::ROLE_STUDENT  => $this->student,
            self::ROLE_PARENT   => $this->guardian,
            self::ROLE_TEACHER => $this->teacher,
            default             => null,
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

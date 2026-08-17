<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Branch extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'institution_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'is_main',
        'is_active',
    ];

    protected $casts = [
        'is_main'   => 'boolean',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        // Branch rows are always scoped to their parent institution.
        // Applied directly (not via BelongsToInstitution trait) because
        // Branch itself has no institution-independent identity.
        static::addGlobalScope(new InstitutionScope());

        static::creating(function (Branch $branch) {
            if (auth()->check()) {
                $branch->institution_id ??= auth()->user()->institution_id;
            }
        });

        // Prevent deleting the Main Branch — every institution must
        // always retain exactly one main branch.
        static::deleting(function (Branch $branch) {
            if ($branch->is_main) {
                throw new \RuntimeException('The main branch cannot be deleted.');
            }
        });
    }

    // ── Relations ─────────────────────────────────────────

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForInstitution($query, int $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    // ── Helpers ───────────────────────────────────────────

    public function label(): string
    {
        return $this->is_main
            ? $this->name . ' (Main)'
            : $this->name;
    }

    // ── Activity Log ──────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
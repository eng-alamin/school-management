<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OfficeExpense extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(OfficeAccount::class, 'account_id');
    }

    public function head()
    {
        return $this->belongsTo(OfficeHead::class, 'head_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Generate a unique voucher number per institution, per year.
     * Format: EXP-2026-0001
     */
    public static function generateVoucherNo(int $institutionId): string
    {
        $year = now()->format('Y');
        $prefix = "EXP-{$year}-";

        return DB::transaction(function () use ($institutionId, $prefix) {
            $last = static::withTrashed()
                ->where('institution_id', $institutionId)
                ->where('voucher_no', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextNumber = $last
                ? ((int) substr($last->voucher_no, -4)) + 1
                : 1;

            return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
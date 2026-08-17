<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToInstitution;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'branch_id',
        'type',
        'invoice_no',
        'month',
        'year',
        'total_amount',
        'discount',
        'payable_amount',
        'status',
        'due_date',
        'paid_at',
        'transaction_id',
        'val_id',
        'payment_method',
        'meta',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'paid_at'        => 'datetime',
        'total_amount'   => 'decimal:2',
        'discount'       => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'meta'           => 'array',
    ];

    public const STATUSES = ['free', 'pending', 'paid', 'overdue', 'failed'];

    public const TYPE_REGISTRATION = 'registration';
    public const TYPE_BILLING      = 'billing';

    public function institution()
    {
        return $this->belongsTo(Institution::class)->withoutGlobalScopes();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public static function statusMeta(string $status): array
    {
        return match ($status) {
            'paid'    => ['label' => 'Paid',    'badge' => 'badge-active',  'hex' => '#16a34a'],
            'free'    => ['label' => 'Free',    'badge' => 'badge-active',  'hex' => '#16a34a'],
            'pending' => ['label' => 'Pending', 'badge' => 'badge-pending', 'hex' => '#d97706'],
            'overdue' => ['label' => 'Overdue', 'badge' => 'badge-overdue', 'hex' => '#dc2626'],
            'failed'  => ['label' => 'Failed',  'badge' => 'badge-overdue', 'hex' => '#dc2626'],
            default   => ['label' => ucfirst($status), 'badge' => 'badge-pending', 'hex' => '#d97706'],
        };
    }
}
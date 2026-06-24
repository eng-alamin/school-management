<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

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
        // due_date null হলে (যেমন registration টাইপ invoice) overdue ধরা যাবে না
        return $this->status === 'pending'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }
}
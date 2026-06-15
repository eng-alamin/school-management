<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(School::class)->withoutGlobalScopes();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }
}

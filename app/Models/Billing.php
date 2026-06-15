<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'billing_month' => 'date',
        'due_date'      => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

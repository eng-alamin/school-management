<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $guarded = [];
    
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}

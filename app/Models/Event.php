<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Event extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function type()
    {
        return $this->belongsTo(EventType::class);
    }

    public function eventClasses()
    {
        return $this->hasMany(EventClass::class);
    }

    public function eventSections()
    {
        return $this->hasMany(EventSection::class);
    }
}

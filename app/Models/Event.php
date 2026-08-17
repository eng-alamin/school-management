<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;

    protected $guarded = [];

    public function eventType()
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

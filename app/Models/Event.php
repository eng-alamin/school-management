<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;

class Event extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

    protected $guarded = [];

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function eventClass()
    {
        return $this->hasOne(EventClass::class);
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

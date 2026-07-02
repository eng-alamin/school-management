<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class AiChatMessage extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

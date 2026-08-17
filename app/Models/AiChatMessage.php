<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;

class AiChatMessage extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

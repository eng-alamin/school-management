<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class OfficeExpense extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(OfficeAccount::class, 'account_id');
    }

    public function head()
    {
        return $this->belongsTo(OfficeHead::class, 'head_id');
    }
}

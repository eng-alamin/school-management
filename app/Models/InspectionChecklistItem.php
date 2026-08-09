<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionChecklistItem extends Model
{
    protected $fillable = [
        'category',
        'criterion',
        'max_score',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_score' => 'integer',
        'sort_order' => 'integer',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(InspectionResult::class, 'checklist_item_id');
    }
}
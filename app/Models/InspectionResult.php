<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionResult extends Model
{
    protected $fillable = [
        'inspection_id',
        'checklist_item_id',
        'score',
        'remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(InspectionChecklistItem::class, 'checklist_item_id');
    }
}
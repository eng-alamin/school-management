<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemErrorLog extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_RESOLVED = 'resolved';

    public const SORTABLE_FIELDS = [
        'id',
        'exception_class',
        'panel',
        'component',
        'status',
        'created_at',
    ];

    protected $fillable = [
        'institution_id',
        'branch_id',
        'user_id',
        'user_role',
        'panel',
        'component',
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'context',
        'url',
        'method',
        'ip',
        'status',
    ];

    protected $casts = [
        'context' => 'array',
        'line'    => 'integer',
    ];

    // Note: এই মডেলে ইচ্ছাকৃতভাবে BelongsToInstitution / BranchScope trait ব্যবহার করা হয়নি,
    // কারণ Super Admin কে সব institution/branch এর error দেখতে হবে।
    // সব query তে explicit institution_id / branch_id ফিল্টার করতে হবে যেখানে দরকার।

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
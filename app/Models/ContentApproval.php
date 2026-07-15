<?php

namespace App\Models;

use App\Enums\ContentApprovalStatus;
use Database\Factories\ContentApprovalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentApproval extends Model
{
    /** @use HasFactory<ContentApprovalFactory> */
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'revision_number',
        'submitted_by_id',
        'reviewed_by_id',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'scheduled_for',
        'published_at',
        'archived_at',
        'review_notes',
        'rejection_reason',
        'is_fast_track',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentApprovalStatus::class,
            'submitted_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
            'scheduled_for' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'is_fast_track' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }
}

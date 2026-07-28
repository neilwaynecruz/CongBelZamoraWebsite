<?php

namespace App\Models;

use App\Enums\ContentApprovalStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ContentApprovalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property ContentApprovalStatus $status
 * @property int $revision_number
 * @property int|null $submitted_by_id
 * @property int|null $reviewed_by_id
 * @property CarbonImmutable|null $submitted_at
 * @property CarbonImmutable|null $reviewed_at
 * @property CarbonImmutable|null $approved_at
 * @property CarbonImmutable|null $scheduled_for
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 * @property CarbonImmutable|null $unpublished_at
 * @property CarbonImmutable|null $valid_until
 * @property array<string, mixed>|null $metadata
 */
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
        'change_summary',
        'archive_reason',
        'is_fast_track',
        'valid_until',
        'metadata',
        'unpublished_at',
        'rollback_snapshot_id',
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
            'unpublished_at' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
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

    /**
     * @return HasMany<ContentSnapshot, $this>
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(ContentSnapshot::class)->latest('version_number');
    }

    /**
     * @return BelongsTo<ContentSnapshot, $this>
     */
    public function rollbackSnapshot(): BelongsTo
    {
        return $this->belongsTo(ContentSnapshot::class, 'rollback_snapshot_id');
    }
}

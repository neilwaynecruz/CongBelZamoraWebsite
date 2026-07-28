<?php

namespace App\Models;

use App\Enums\ContentSnapshotType;
use Carbon\CarbonImmutable;
use Database\Factories\ContentSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property ContentSnapshotType $snapshot_type
 * @property int $version_number
 * @property int $approval_revision_number
 * @property array<string, mixed> $payload
 * @property array<string, mixed>|null $diff_from_previous
 * @property list<string>|null $governance_issues
 * @property CarbonImmutable $captured_at
 */
class ContentSnapshot extends Model
{
    /** @use HasFactory<ContentSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'content_approval_id',
        'approvable_type',
        'approvable_id',
        'version_number',
        'approval_revision_number',
        'snapshot_type',
        'captured_by_id',
        'change_summary',
        'payload',
        'payload_hash',
        'diff_from_previous',
        'is_publishable',
        'governance_issues',
        'captured_at',
        'published_at',
        'rolled_back_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_type' => ContentSnapshotType::class,
            'payload' => 'array',
            'diff_from_previous' => 'array',
            'is_publishable' => 'boolean',
            'governance_issues' => 'array',
            'captured_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'rolled_back_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<ContentApproval, $this>
     */
    public function approval(): BelongsTo
    {
        return $this->belongsTo(ContentApproval::class, 'content_approval_id');
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
    public function capturedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captured_by_id');
    }
}

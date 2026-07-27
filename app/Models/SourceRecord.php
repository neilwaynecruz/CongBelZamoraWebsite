<?php

namespace App\Models;

use App\Enums\SourceType;
use Carbon\CarbonImmutable;
use Database\Factories\SourceRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $url
 * @property SourceType $source_type
 * @property string $source_tier
 * @property string $public_status
 * @property CarbonImmutable|null $verified_at
 * @property CarbonImmutable|null $last_reviewed_at
 * @property CarbonImmutable|null $next_review_at
 * @property CarbonImmutable|null $effective_at
 * @property CarbonImmutable|null $expires_at
 * @property array<string, mixed>|null $metadata
 */
class SourceRecord extends Model
{
    /** @use HasFactory<SourceRecordFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'sourceable_type',
        'sourceable_id',
        'title',
        'url',
        'source_type',
        'source_tier',
        'description',
        'content_owner_id',
        'verified_by_id',
        'verified_at',
        'last_reviewed_at',
        'next_review_at',
        'effective_at',
        'expires_at',
        'public_status',
        'internal_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => SourceType::class,
            'verified_at' => 'immutable_datetime',
            'last_reviewed_at' => 'immutable_datetime',
            'next_review_at' => 'immutable_datetime',
            'effective_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function contentOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_owner_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    /**
     * @param  Builder<SourceRecord>  $query
     * @return Builder<SourceRecord>
     */
    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query
            ->whereNotNull('next_review_at')
            ->where('next_review_at', '<=', now());
    }
}

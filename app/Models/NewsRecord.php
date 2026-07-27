<?php

namespace App\Models;

use Database\Factories\NewsRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property array<string, mixed>|null $metadata
 */
class NewsRecord extends Model
{
    /** @use HasFactory<NewsRecordFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'news_category_id',
        'news_status_id',
        'title',
        'slug',
        'published_at_label',
        'published_at',
        'public_updated_at_label',
        'public_updated_at',
        'source_label',
        'source_url',
        'summary',
        'official_update',
        'next_step',
        'stale_note',
        'owner_label',
        'source_verified_at',
        'review_due_at',
        'content_owner_id',
        'approved_by_id',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'immutable_datetime',
            'public_updated_at' => 'immutable_datetime',
            'source_verified_at' => 'immutable_datetime',
            'review_due_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<NewsCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    /**
     * @return BelongsTo<NewsStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(NewsStatus::class, 'news_status_id');
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
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * @return HasMany<NewsMediaAsset, $this>
     */
    public function mediaAssets(): HasMany
    {
        return $this->hasMany(NewsMediaAsset::class)->orderBy('sort_order');
    }

    /**
     * @return MorphMany<SourceRecord, $this>
     */
    public function sourceRecords(): MorphMany
    {
        return $this->morphMany(SourceRecord::class, 'sourceable');
    }

    /**
     * @return MorphMany<ContentApproval, $this>
     */
    public function approvalRecords(): MorphMany
    {
        return $this->morphMany(ContentApproval::class, 'approvable');
    }

    /**
     * @param  Builder<NewsRecord>  $query
     * @return Builder<NewsRecord>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<NewsRecord>  $query
     * @return Builder<NewsRecord>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at')->orderBy('title');
    }
}

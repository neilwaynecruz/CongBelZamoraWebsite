<?php

namespace App\Models;

use Database\Factories\PublicPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property array<string, mixed> $props
 * @property array<string, mixed>|null $metadata
 */
class PublicPage extends Model
{
    /** @use HasFactory<PublicPageFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'component',
        'props',
        'owner_label',
        'source_label',
        'source_url',
        'verified_at',
        'review_due_at',
        'content_owner_id',
        'approved_by_id',
        'published_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'props' => 'array',
            'verified_at' => 'immutable_datetime',
            'review_due_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
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
     * @param  Builder<PublicPage>  $query
     * @return Builder<PublicPage>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}

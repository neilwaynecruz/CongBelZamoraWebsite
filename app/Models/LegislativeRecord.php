<?php

namespace App\Models;

use Database\Factories\LegislativeRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property array<string, mixed>|null $metadata
 */
class LegislativeRecord extends Model
{
    /** @use HasFactory<LegislativeRecordFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'legislative_role_id',
        'legislative_status_id',
        'legislative_topic_id',
        'bill_number',
        'slug',
        'official_title',
        'public_title',
        'congress',
        'committee',
        'filed_at_label',
        'filed_at',
        'verified_at',
        'plain_language_summary',
        'why_it_matters',
        'san_juan_relevance',
        'official_source_label',
        'official_source_url',
        'content_owner_id',
        'approved_by_id',
        'published_at',
        'review_due_at',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'filed_at' => 'immutable_date',
            'verified_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'review_due_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<LegislativeRole, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(LegislativeRole::class, 'legislative_role_id');
    }

    /**
     * @return BelongsTo<LegislativeStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(LegislativeStatus::class, 'legislative_status_id');
    }

    /**
     * @return BelongsTo<LegislativeTopic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(LegislativeTopic::class, 'legislative_topic_id');
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
     * @return HasMany<LegislativeSourceHistory, $this>
     */
    public function sourceHistories(): HasMany
    {
        return $this->hasMany(LegislativeSourceHistory::class)->latest('checked_at');
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
     * @param  Builder<LegislativeRecord>  $query
     * @return Builder<LegislativeRecord>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<LegislativeRecord>  $query
     * @return Builder<LegislativeRecord>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('bill_number');
    }
}

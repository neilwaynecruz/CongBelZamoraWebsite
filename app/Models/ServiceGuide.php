<?php

namespace App\Models;

use App\Enums\ResponsibleOfficeType;
use Database\Factories\ServiceGuideFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property ResponsibleOfficeType $responsible_office_type
 * @property array<string, mixed>|null $metadata
 */
class ServiceGuide extends Model
{
    /** @use HasFactory<ServiceGuideFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'label',
        'concern',
        'responsible_office_type',
        'responsible_office_label',
        'district_office_role',
        'can_district_office_directly_handle',
        'explanation',
        'next_step',
        'contact_label',
        'contact_href',
        'verified_at',
        'content_owner_id',
        'approved_by_id',
        'published_at',
        'review_due_at',
        'is_featured',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'responsible_office_type' => ResponsibleOfficeType::class,
            'can_district_office_directly_handle' => 'boolean',
            'verified_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'review_due_at' => 'immutable_datetime',
            'is_featured' => 'boolean',
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
     * @return HasMany<ServiceGuideStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ServiceGuideStep::class)->orderBy('sort_order');
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
     * @param  Builder<ServiceGuide>  $query
     * @return Builder<ServiceGuide>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<ServiceGuide>  $query
     * @return Builder<ServiceGuide>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}

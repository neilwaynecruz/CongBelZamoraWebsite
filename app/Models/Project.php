<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
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
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'project_category_id',
        'project_status_id',
        'primary_partner_id',
        'title',
        'slug',
        'barangay',
        'location',
        'responsible_office',
        'partner_label',
        'summary',
        'evidence_summary',
        'impact_note',
        'image_guidance',
        'source_label',
        'source_url',
        'verified_at',
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
            'verified_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'review_due_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<ProjectCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    /**
     * @return BelongsTo<ProjectStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    /**
     * @return BelongsTo<ProjectPartner, $this>
     */
    public function primaryPartner(): BelongsTo
    {
        return $this->belongsTo(ProjectPartner::class, 'primary_partner_id');
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
     * @return HasMany<ProjectEvidence, $this>
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(ProjectEvidence::class)->orderBy('sort_order');
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
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}

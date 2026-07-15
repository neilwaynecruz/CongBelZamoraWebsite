<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property ProgramStatus $status
 * @property ResponsibleOfficeType $responsible_office_type
 * @property array<string, mixed>|null $metadata
 */
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    protected $fillable = [
        'program_category_id',
        'title',
        'slug',
        'href',
        'sector',
        'status',
        'summary',
        'description',
        'responsible_office_type',
        'responsible_office_label',
        'partner_agency',
        'beneficiary_group',
        'application_period',
        'benefit',
        'source_label',
        'source_url',
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
            'status' => ProgramStatus::class,
            'responsible_office_type' => ResponsibleOfficeType::class,
            'verified_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'review_due_at' => 'immutable_datetime',
            'is_featured' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<ProgramCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgramCategory::class, 'program_category_id');
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
     * @return HasMany<ProgramRequirement, $this>
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(ProgramRequirement::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<ProgramUpdate, $this>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(ProgramUpdate::class);
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
     * @param  Builder<Program>  $query
     * @return Builder<Program>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Program>  $query
     * @return Builder<Program>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}

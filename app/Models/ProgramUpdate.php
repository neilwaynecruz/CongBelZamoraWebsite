<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use Database\Factories\ProgramUpdateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProgramUpdate extends Model
{
    /** @use HasFactory<ProgramUpdateFactory> */
    use HasFactory;

    protected $fillable = [
        'program_id',
        'title',
        'summary',
        'status',
        'source_label',
        'source_url',
        'verified_at',
        'content_owner_id',
        'published_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProgramStatus::class,
            'verified_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function contentOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_owner_id');
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
     * @param  Builder<ProgramUpdate>  $query
     * @return Builder<ProgramUpdate>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}

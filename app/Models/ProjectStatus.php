<?php

namespace App\Models;

use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Models\Concerns\HasSourceRecords;
use Database\Factories\ProjectStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property ProjectStatusValue $value
 * @property ProjectStatusTone $tone
 */
class ProjectStatus extends Model
{
    /** @use HasFactory<ProjectStatusFactory> */
    use HasFactory, HasSourceRecords;

    protected $fillable = [
        'name',
        'slug',
        'value',
        'label',
        'tone',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => ProjectStatusValue::class,
            'tone' => ProjectStatusTone::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @param  Builder<ProjectStatus>  $query
     * @return Builder<ProjectStatus>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<ProjectStatus>  $query
     * @return Builder<ProjectStatus>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}

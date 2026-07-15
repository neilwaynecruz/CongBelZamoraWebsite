<?php

namespace App\Models;

use App\Enums\LegislativeStatusTone;
use Database\Factories\LegislativeStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property LegislativeStatusTone $tone
 */
class LegislativeStatus extends Model
{
    /** @use HasFactory<LegislativeStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'label',
        'tone',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tone' => LegislativeStatusTone::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<LegislativeRecord, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(LegislativeRecord::class);
    }

    /**
     * @param  Builder<LegislativeStatus>  $query
     * @return Builder<LegislativeStatus>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<LegislativeStatus>  $query
     * @return Builder<LegislativeStatus>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}

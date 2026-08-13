<?php

namespace App\Models;

use App\Models\Concerns\HasSourceRecords;
use Database\Factories\LegislativeTopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegislativeTopic extends Model
{
    /** @use HasFactory<LegislativeTopicFactory> */
    use HasFactory, HasSourceRecords;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
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
     * @param  Builder<LegislativeTopic>  $query
     * @return Builder<LegislativeTopic>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<LegislativeTopic>  $query
     * @return Builder<LegislativeTopic>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}

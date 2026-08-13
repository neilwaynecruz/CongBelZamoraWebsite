<?php

namespace App\Models;

use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Models\Concerns\HasSourceRecords;
use Database\Factories\NewsStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property NewsStatusValue $value
 * @property NewsStatusTone $tone
 */
class NewsStatus extends Model
{
    /** @use HasFactory<NewsStatusFactory> */
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
            'value' => NewsStatusValue::class,
            'tone' => NewsStatusTone::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<NewsRecord, $this>
     */
    public function newsRecords(): HasMany
    {
        return $this->hasMany(NewsRecord::class);
    }

    /**
     * @param  Builder<NewsStatus>  $query
     * @return Builder<NewsStatus>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<NewsStatus>  $query
     * @return Builder<NewsStatus>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}

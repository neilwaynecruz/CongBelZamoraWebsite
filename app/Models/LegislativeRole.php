<?php

namespace App\Models;

use App\Enums\LegislativeRoleValue;
use Database\Factories\LegislativeRoleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property LegislativeRoleValue $value
 */
class LegislativeRole extends Model
{
    /** @use HasFactory<LegislativeRoleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'value',
        'label',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => LegislativeRoleValue::class,
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
     * @param  Builder<LegislativeRole>  $query
     * @return Builder<LegislativeRole>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<LegislativeRole>  $query
     * @return Builder<LegislativeRole>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}

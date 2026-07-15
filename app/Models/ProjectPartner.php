<?php

namespace App\Models;

use Database\Factories\ProjectPartnerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPartner extends Model
{
    /** @use HasFactory<ProjectPartnerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'office_type',
        'website_url',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'primary_partner_id');
    }

    /**
     * @param  Builder<ProjectPartner>  $query
     * @return Builder<ProjectPartner>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

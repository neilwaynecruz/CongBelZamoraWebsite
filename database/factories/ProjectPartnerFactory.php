<?php

namespace Database\Factories;

use App\Models\ProjectPartner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectPartner>
 */
class ProjectPartnerFactory extends Factory
{
    protected $model = ProjectPartner::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'office_type' => fake()->randomElement(['city_government', 'national_agency', 'partner_office']),
            'website_url' => fake()->url(),
            'notes' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}

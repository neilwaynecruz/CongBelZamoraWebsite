<?php

namespace Database\Factories;

use App\Enums\LegislativeRoleValue;
use App\Models\LegislativeRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LegislativeRole>
 */
class LegislativeRoleFactory extends Factory
{
    protected $model = LegislativeRole::class;

    public function definition(): array
    {
        $role = fake()->randomElement(LegislativeRoleValue::cases());

        return [
            'name' => $role->label(),
            'slug' => Str::slug($role->label()).'-'.fake()->unique()->numberBetween(100, 999),
            'value' => $role,
            'label' => $role->label(),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentApproval>
 */
class ContentApprovalFactory extends Factory
{
    protected $model = ContentApproval::class;

    public function definition(): array
    {
        return [
            'status' => ContentApprovalStatus::Draft,
            'revision_number' => 1,
            'submitted_by_id' => User::factory(),
            'metadata' => ['factory' => true],
        ];
    }
}

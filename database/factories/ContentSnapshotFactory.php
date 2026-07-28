<?php

namespace Database\Factories;

use App\Enums\ContentSnapshotType;
use App\Models\ContentApproval;
use App\Models\ContentSnapshot;
use App\Models\PublicPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentSnapshot>
 */
class ContentSnapshotFactory extends Factory
{
    protected $model = ContentSnapshot::class;

    public function definition(): array
    {
        $page = PublicPage::factory()->create();

        return [
            'content_approval_id' => ContentApproval::factory()->for($page, 'approvable'),
            'approvable_type' => $page::class,
            'approvable_id' => $page->id,
            'version_number' => 1,
            'approval_revision_number' => 1,
            'snapshot_type' => ContentSnapshotType::Draft,
            'captured_by_id' => User::factory(),
            'change_summary' => 'Factory snapshot.',
            'payload' => [
                'model' => $page::class,
                'id' => $page->id,
                'attributes' => $page->getAttributes(),
            ],
            'payload_hash' => hash('sha256', json_encode($page->getAttributes(), JSON_THROW_ON_ERROR)),
            'diff_from_previous' => [],
            'is_publishable' => true,
            'governance_issues' => [],
            'captured_at' => now(),
        ];
    }
}

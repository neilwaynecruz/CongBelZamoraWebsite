<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\SourceType;
use App\Models\ContentApproval;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase7GovernanceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_governance_tables_and_user_role_column_exist(): void
    {
        expect(Schema::hasColumn('users', 'admin_role'))->toBeTrue()
            ->and(Schema::hasTable('source_records'))->toBeTrue()
            ->and(Schema::hasTable('content_approvals'))->toBeTrue()
            ->and(Schema::hasTable('audit_logs'))->toBeTrue()
            ->and(Schema::hasTable('inquiry_categories'))->toBeTrue()
            ->and(Schema::hasTable('inquiries'))->toBeTrue();
    }

    public function test_governance_models_cast_status_and_source_enums(): void
    {
        $owner = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $source = SourceRecord::factory()->create([
            'content_owner_id' => $owner->id,
            'verified_by_id' => $owner->id,
            'source_type' => SourceType::PrimaryOfficial,
        ]);

        $approval = ContentApproval::factory()->create([
            'status' => ContentApprovalStatus::ForReview,
            'submitted_by_id' => $owner->id,
        ]);

        expect($source->source_type)->toBe(SourceType::PrimaryOfficial)
            ->and($source->contentOwner->is($owner))->toBeTrue()
            ->and($approval->status)->toBe(ContentApprovalStatus::ForReview)
            ->and($approval->submitter->is($owner))->toBeTrue();
    }
}

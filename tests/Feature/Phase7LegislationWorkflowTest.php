<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\LegislativeRoleValue;
use App\Enums\LegislativeStatusTone;
use App\Filament\Resources\LegislativeRecordResource;
use App\Models\ContentApproval;
use App\Models\LegislativeRecord;
use App\Models\LegislativeRole;
use App\Models\LegislativeSourceHistory;
use App\Models\LegislativeStatus;
use App\Models\LegislativeTopic;
use App\Models\SourceRecord;
use App\Models\User;
use Database\Seeders\LegislativeRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase7LegislationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_legislative_schema_exists(): void
    {
        expect(Schema::hasTable('legislative_roles'))->toBeTrue()
            ->and(Schema::hasTable('legislative_statuses'))->toBeTrue()
            ->and(Schema::hasTable('legislative_topics'))->toBeTrue()
            ->and(Schema::hasTable('legislative_records'))->toBeTrue()
            ->and(Schema::hasTable('legislative_source_histories'))->toBeTrue()
            ->and(Schema::hasColumn('legislative_records', 'official_source_url'))->toBeTrue()
            ->and(Schema::hasColumn('legislative_records', 'review_due_at'))->toBeTrue();
    }

    public function test_legislative_models_cast_enums_and_keep_relationships(): void
    {
        $role = LegislativeRole::factory()->create([
            'value' => LegislativeRoleValue::CoAuthor,
            'label' => 'Co-Author',
        ]);
        $status = LegislativeStatus::factory()->create([
            'tone' => LegislativeStatusTone::Pending,
            'label' => 'Pending Committee Review',
        ]);
        $topic = LegislativeTopic::factory()->create([
            'name' => 'Education and Digital Access',
        ]);
        $record = LegislativeRecord::factory()
            ->for($role, 'role')
            ->for($status, 'status')
            ->for($topic, 'topic')
            ->create();

        LegislativeSourceHistory::factory()
            ->for($record, 'legislativeRecord')
            ->create(['status_snapshot' => 'Pending Committee Review']);

        expect($record->role->value)->toBe(LegislativeRoleValue::CoAuthor)
            ->and($record->status->tone)->toBe(LegislativeStatusTone::Pending)
            ->and($record->topic->is($topic))->toBeTrue()
            ->and($record->sourceHistories)->toHaveCount(1);
    }

    public function test_seeded_legislation_has_source_history_and_approval_records(): void
    {
        $this->seed(LegislativeRecordSeeder::class);

        $record = LegislativeRecord::query()->where('bill_number', 'HB00123')->firstOrFail();

        expect($record->sourceHistories)->toHaveCount(1)
            ->and($record->sourceRecords)->toHaveCount(1)
            ->and($record->approvalRecords)->toHaveCount(1)
            ->and($record->approvalRecords->first()->status)->toBe(ContentApprovalStatus::Published)
            ->and(SourceRecord::query()->where('sourceable_type', LegislativeRecord::class)->count())->toBe(3)
            ->and(ContentApproval::query()->where('approvable_type', LegislativeRecord::class)->count())->toBe(3);
    }

    public function test_legislation_policies_are_least_privilege(): void
    {
        $legislativeEditor = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $record = LegislativeRecord::factory()->create();

        expect($legislativeEditor->can('update', $record))->toBeTrue()
            ->and($contentAdmin->can('delete', $record))->toBeTrue()
            ->and($reviewer->can('view', $record))->toBeTrue()
            ->and($reviewer->can('approve', $record))->toBeTrue()
            ->and($reviewer->can('update', $record))->toBeFalse()
            ->and($auditor->can('view', $record))->toBeTrue()
            ->and($auditor->can('update', $record))->toBeFalse()
            ->and($inquiryOfficer->can('view', $record))->toBeFalse()
            ->and($programOfficer->can('update', $record))->toBeFalse();
    }

    public function test_filament_legislative_resource_is_role_gated(): void
    {
        $legislativeEditor = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        $this->actingAs($legislativeEditor)
            ->get(LegislativeRecordResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($inquiryOfficer)
            ->get(LegislativeRecordResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_public_legislation_route_uses_backend_records_with_phase_6_contract(): void
    {
        $this->seed(LegislativeRecordSeeder::class);

        $this->get(route('public.legislation'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Legislation/Index')
                ->has('legislativeItems', 3)
                ->where('legislativeItems.0.billNumber', 'HB00123')
                ->where('legislativeItems.0.role', LegislativeRoleValue::PrincipalAuthor->value)
                ->where('legislativeItems.0.statusTone', LegislativeStatusTone::Passed->value)
                ->has('roleOptions')
                ->has('statusOptions')
                ->has('topicOptions')
                ->where('initialFilters.role', 'All roles')
                ->where('verifiedAt', 'July 12, 2026'));

        $this->get(route('public.legislation', ['role' => 'co-author']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Legislation/Index')
                ->where('initialFilters.role', 'Co-Author'));
    }
}

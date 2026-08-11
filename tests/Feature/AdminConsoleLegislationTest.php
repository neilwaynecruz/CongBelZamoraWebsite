<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Enums\LegislativeRoleValue;
use App\Enums\LegislativeStatusTone;
use App\Enums\SourceType;
use App\Filament\Resources\LegislativeRecordResource as FilamentLegislativeRecordResource;
use App\Models\ContentApproval;
use App\Models\LegislativeRecord;
use App\Models\LegislativeRole;
use App\Models\LegislativeSourceHistory;
use App\Models\LegislativeStatus;
use App\Models\LegislativeTopic;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleLegislationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legislation_console_is_policy_protected_and_navigation_is_role_aware(): void
    {
        $legislativeEditor = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        LegislativeRecord::factory()->create(['public_title' => 'Policy Protected Bill']);

        $this->actingAs($legislativeEditor)
            ->get(route('admin-console.legislation.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Legislation/Index')
                ->where('adminConsole.user.role', AdminRole::LegislativeEditor->value)
                ->where('can.create', true)
                ->has('adminConsole.navigation'));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($legislativeEditor))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('Legislation'));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.legislation.index'))
            ->assertForbidden();
    }

    public function test_legislation_editor_create_and_edit_routes_are_policy_protected(): void
    {
        $legislativeEditor = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        [$role, $status, $topic] = $this->legislativeTaxonomy();
        $record = LegislativeRecord::factory()
            ->for($role, 'role')
            ->for($status, 'status')
            ->for($topic, 'topic')
            ->create(['public_title' => 'Editable Legislative Record']);

        $this->actingAs($legislativeEditor)
            ->get(route('admin-console.legislation.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Legislation/Edit')
                ->where('form.mode', 'create')
                ->where('form.options.roles.0.label', 'Principal Author')
                ->where('form.options.statuses.0.label', 'Pending committee review')
                ->where('form.options.topics.0.label', 'Education'));

        $this->actingAs($legislativeEditor)
            ->get(route('admin-console.legislation.edit', $record))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Legislation/Edit')
                ->where('form.mode', 'edit')
                ->where('form.values.public_title', 'Editable Legislative Record'));

        $this->actingAs($reviewer)
            ->get(route('admin-console.legislation.create'))
            ->assertForbidden();

        $this->actingAs($reviewer)
            ->get(route('admin-console.legislation.edit', $record))
            ->assertForbidden();
    }

    public function test_legislation_editor_validates_required_source_and_governance_fields(): void
    {
        $staff = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.legislation.store'), [
                'public_title' => '',
                'slug' => 'Not Valid',
                'approval' => ['submit_for_review' => true],
            ])
            ->assertSessionHasErrors([
                'legislative_role_id',
                'legislative_status_id',
                'legislative_topic_id',
                'bill_number',
                'slug',
                'official_title',
                'public_title',
                'official_source_url',
                'source_record.title',
                'approval.change_summary',
            ]);
    }

    public function test_legislation_editor_persists_record_source_history_and_source_register(): void
    {
        $staff = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        [$role, $status, $topic] = $this->legislativeTaxonomy();

        $this->actingAs($staff)
            ->post(route('admin-console.legislation.store'), $this->validLegislativePayload($role, $status, $topic))
            ->assertRedirect();

        $record = LegislativeRecord::query()->where('slug', 'hb-12345-teachers-support')->firstOrFail();

        $this->assertSame($staff->id, $record->content_owner_id);
        $this->assertDatabaseHas('legislative_records', [
            'id' => $record->id,
            'bill_number' => 'HB12345',
            'public_title' => 'Teachers Support and Classroom Readiness Act',
            'official_source_url' => 'https://www.congress.gov.ph/',
        ]);
        $this->assertDatabaseHas('legislative_source_histories', [
            'legislative_record_id' => $record->id,
            'source_label' => 'House bill status page',
            'status_snapshot' => 'Filed and referred to committee',
            'checked_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('source_records', [
            'sourceable_type' => $record->getMorphClass(),
            'sourceable_id' => $record->id,
            'title' => 'House bill status page',
            'source_type' => SourceType::PrimaryOfficial->value,
            'verified_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $record->getMorphClass(),
            'auditable_id' => $record->id,
            'event' => 'legislative_record.created',
        ]);
    }

    public function test_legislation_editor_updates_nested_source_history_transactionally(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        [$role, $status, $topic] = $this->legislativeTaxonomy();
        $record = LegislativeRecord::factory()
            ->for($role, 'role')
            ->for($status, 'status')
            ->for($topic, 'topic')
            ->create([
                'public_title' => 'Old Legislative Record',
                'slug' => 'old-legislative-record',
                'content_owner_id' => $staff->id,
            ]);
        $history = LegislativeSourceHistory::factory()->for($record, 'legislativeRecord')->create([
            'source_label' => 'Old history',
        ]);

        $payload = $this->validLegislativePayload($role, $status, $topic, [
            'public_title' => 'Updated Legislative Record',
            'slug' => 'updated-legislative-record',
            'source_histories' => [
                [
                    'source_label' => 'Updated House source',
                    'source_url' => 'https://www.congress.gov.ph/',
                    'status_snapshot' => 'Committee review updated',
                    'checked_at' => now()->subDay()->toDateString(),
                    'notes' => 'Status checked against the official House source.',
                ],
                [
                    'source_label' => 'Official member page',
                    'source_url' => 'https://www.congress.gov.ph/',
                    'status_snapshot' => 'Author role verified',
                    'checked_at' => now()->subDay()->toDateString(),
                    'notes' => 'Role checked for public tracker display.',
                ],
            ],
        ]);

        $this->actingAs($staff)
            ->put(route('admin-console.legislation.update', $record), $payload)
            ->assertRedirect(route('admin-console.legislation.show', $record));

        $record->refresh();

        $this->assertSame('Updated Legislative Record', $record->public_title);
        $this->assertSame('updated-legislative-record', $record->slug);
        $this->assertSame(2, $record->sourceHistories()->count());
        $this->assertSoftDeleted('legislative_source_histories', ['id' => $history->id]);
        $this->assertDatabaseHas('legislative_source_histories', [
            'legislative_record_id' => $record->id,
            'status_snapshot' => 'Author role verified',
        ]);
    }

    public function test_legislation_editor_can_submit_saved_record_to_approval_workflow(): void
    {
        $staff = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        [$role, $status, $topic] = $this->legislativeTaxonomy();

        $payload = $this->validLegislativePayload($role, $status, $topic, [
            'approval' => [
                'submit_for_review' => true,
                'change_summary' => 'Created the legislative tracker record, verified official source, and added source history.',
                'is_fast_track' => false,
                'valid_until' => now()->addMonth()->toDateString(),
            ],
        ]);

        $this->actingAs($staff)
            ->post(route('admin-console.legislation.store'), $payload)
            ->assertRedirect();

        $record = LegislativeRecord::query()->where('slug', 'hb-12345-teachers-support')->firstOrFail();
        $approval = ContentApproval::query()
            ->where('approvable_type', $record->getMorphClass())
            ->where('approvable_id', $record->id)
            ->firstOrFail();

        $this->assertSame(ContentApprovalStatus::ForReview, $approval->status);
        $this->assertSame($staff->id, $approval->submitted_by_id);
        $this->assertDatabaseHas('content_snapshots', [
            'content_approval_id' => $approval->id,
            'snapshot_type' => ContentSnapshotType::Submitted->value,
            'captured_by_id' => $staff->id,
        ]);
    }

    public function test_legislation_console_filters_by_search_role_status_and_topic(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        [$role, $status, $topic] = $this->legislativeTaxonomy();
        $coAuthor = LegislativeRole::factory()->create([
            'value' => LegislativeRoleValue::CoAuthor,
            'label' => 'Co-Author',
        ]);

        LegislativeRecord::factory()
            ->for($role, 'role')
            ->for($status, 'status')
            ->for($topic, 'topic')
            ->create([
                'bill_number' => 'HB7788',
                'public_title' => 'Education Desk Support Bill',
            ]);
        LegislativeRecord::factory()
            ->for($coAuthor, 'role')
            ->create([
                'bill_number' => 'HB9999',
                'public_title' => 'Different Bill',
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.legislation.index', [
                'search' => 'Education',
                'role' => $role->id,
                'status' => $status->id,
                'topic' => $topic->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Legislation/Index')
                ->where('legislativeRecords.data.0.billNumber', 'HB7788')
                ->where('legislativeRecords.data.0.role.label', 'Principal Author')
                ->where('legislativeRecords.data.0.topic.name', 'Education')
                ->where('legislativeRecords.meta.total', 1)
                ->where('filters.search', 'Education')
                ->where('filters.role', (string) $role->id));
    }

    public function test_legislation_dossier_exposes_source_approval_lifecycle_and_fallback_status(): void
    {
        $staff = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        [$role, $status, $topic] = $this->legislativeTaxonomy();
        $record = LegislativeRecord::factory()
            ->for($role, 'role')
            ->for($status, 'status')
            ->for($topic, 'topic')
            ->create([
                'bill_number' => 'HB4411',
                'public_title' => 'Education Governance Bill',
                'content_owner_id' => $staff->id,
                'approved_by_id' => $reviewer->id,
                'review_due_at' => now()->addDays(20),
            ]);
        LegislativeSourceHistory::factory()
            ->for($record, 'legislativeRecord')
            ->for($reviewer, 'checker')
            ->create(['status_snapshot' => 'Committee status verified']);
        SourceRecord::factory()
            ->for($record, 'sourceable')
            ->for($staff, 'contentOwner')
            ->for($reviewer, 'verifier')
            ->create(['title' => 'Official House legislative source']);
        $approval = ContentApproval::factory()
            ->for($record, 'approvable')
            ->for($staff, 'submitter')
            ->for($reviewer, 'reviewer')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'revision_number' => 2,
                'submitted_at' => now(),
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.legislation.show', $record))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Legislation/Show')
                ->where('legislativeRecord.billNumber', 'HB4411')
                ->where('legislativeRecord.source.recordTitle', 'Official House legislative source')
                ->where('legislativeRecord.sourceHistories.0.statusSnapshot', 'Committee status verified')
                ->where('legislativeRecord.governance.approval.id', $approval->id)
                ->where('legislativeRecord.lifecycle.archiveUrl', route('admin-console.legislation.archive', $record))
                ->where('legislativeRecord.can.update', true)
                ->where('filamentFallbackUrl', FilamentLegislativeRecordResource::getUrl('edit', ['record' => $record])));
    }

    public function test_legislation_lifecycle_and_command_search_use_custom_routes(): void
    {
        $administrator = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $record = LegislativeRecord::factory()->create([
            'public_title' => 'Custom Searchable Legislative Record',
        ]);

        $this->actingAs($administrator)
            ->getJson(route('admin-console.search', ['term' => 'Custom Searchable']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'Legislation',
                'title' => 'Custom Searchable Legislative Record',
                'href' => route('admin-console.legislation.show', $record),
                'usesLegacyFallback' => false,
            ]);

        $this->actingAs($administrator)
            ->post(route('admin-console.legislation.archive', $record), [
                'reason' => 'Record archived after source reconciliation found a replacement.',
            ])
            ->assertRedirect();

        $this->assertSoftDeleted('legislative_records', ['id' => $record->id]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $record->getMorphClass(),
            'auditable_id' => $record->id,
            'event' => 'legislative.record.archived',
        ]);

        $this->actingAs($superAdministrator)
            ->post(route('admin-console.legislation.restore', $record), [
                'reason' => 'Restored after reviewer confirmed this is the authoritative record.',
            ])
            ->assertRedirect();

        $this->assertFalse($record->fresh()->trashed());
    }

    /**
     * @return array{0: LegislativeRole, 1: LegislativeStatus, 2: LegislativeTopic}
     */
    private function legislativeTaxonomy(): array
    {
        $role = LegislativeRole::factory()->create([
            'value' => LegislativeRoleValue::PrincipalAuthor,
            'label' => 'Principal Author',
            'sort_order' => 1,
        ]);
        $status = LegislativeStatus::factory()->create([
            'label' => 'Pending committee review',
            'tone' => LegislativeStatusTone::Pending,
            'sort_order' => 1,
        ]);
        $topic = LegislativeTopic::factory()->create([
            'name' => 'Education',
            'sort_order' => 1,
        ]);

        return [$role, $status, $topic];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validLegislativePayload(
        LegislativeRole $role,
        LegislativeStatus $status,
        LegislativeTopic $topic,
        array $overrides = [],
    ): array {
        $payload = [
            'legislative_role_id' => $role->id,
            'legislative_status_id' => $status->id,
            'legislative_topic_id' => $topic->id,
            'bill_number' => 'HB12345',
            'slug' => 'hb-12345-teachers-support',
            'official_title' => 'An Act Strengthening Support for Public School Teachers and Classroom Readiness',
            'public_title' => 'Teachers Support and Classroom Readiness Act',
            'congress' => '20th Congress',
            'committee' => 'Committee on Basic Education and Culture',
            'filed_at_label' => 'Filed according to the official House source',
            'filed_at' => now()->subMonth()->toDateString(),
            'verified_at' => now()->subDay()->toDateString(),
            'plain_language_summary' => 'Plain-language explanation of the bill for residents reviewing the public tracker.',
            'why_it_matters' => 'This explains the practical issue the measure is intended to address without promising passage.',
            'san_juan_relevance' => 'San Juan residents can use this record to understand the congressional role and official-source status.',
            'official_source_label' => 'House bill status page',
            'official_source_url' => 'https://www.congress.gov.ph/',
            'published_at' => null,
            'review_due_at' => now()->addMonth()->toDateString(),
            'sort_order' => 2,
            'source_record' => [
                'title' => 'House bill status page',
                'url' => 'https://www.congress.gov.ph/',
                'source_type' => SourceType::PrimaryOfficial->value,
                'source_tier' => 'tier_1',
                'public_status' => 'published',
                'description' => 'Official legislative source reviewed by the legislative editor.',
                'verified_at' => now()->subDay()->toDateString(),
                'next_review_at' => now()->addMonth()->toDateString(),
            ],
            'source_histories' => [
                [
                    'source_label' => 'House bill status page',
                    'source_url' => 'https://www.congress.gov.ph/',
                    'status_snapshot' => 'Filed and referred to committee',
                    'checked_at' => now()->subDay()->toDateString(),
                    'notes' => 'Checked against the official House legislative source.',
                ],
            ],
            'approval' => [
                'submit_for_review' => false,
                'change_summary' => '',
                'is_fast_track' => false,
                'valid_until' => null,
            ],
        ];

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        return $payload;
    }
}

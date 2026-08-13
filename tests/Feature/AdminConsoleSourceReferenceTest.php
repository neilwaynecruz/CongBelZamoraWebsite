<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\SourceType;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleSourceReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_records_are_custom_routed_policy_protected_and_navigation_visible(): void
    {
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        SourceRecord::factory()->create(['title' => 'House official source']);

        $this->actingAs($contentAdmin)
            ->get(route('admin-console.source-records.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/SourceRecords/Index')
                ->where('can.create', true)
                ->where('sourceRecords.data.0.title', 'House official source'));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($contentAdmin))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('Source Records'));
        $this->assertTrue($labels->contains('Reference Data'));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.source-records.create'))
            ->assertForbidden();
    }

    public function test_source_record_editor_validates_persists_and_audits(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.source-records.store'), [
                'title' => '',
                'url' => 'not-a-url',
                'source_type' => 'unsafe',
                'source_tier' => 'tier_9',
                'public_status' => 'unknown',
                'metadata_json' => '{bad',
            ])
            ->assertSessionHasErrors(['title', 'url', 'source_type', 'source_tier', 'public_status', 'metadata_json']);

        $this->actingAs($staff)
            ->post(route('admin-console.source-records.store'), $this->validSourcePayload())
            ->assertRedirect();

        $source = SourceRecord::query()->where('title', 'Official scholarship memo')->firstOrFail();

        $this->assertSame(SourceType::PrimaryOfficial, $source->source_type);
        $this->assertSame($staff->id, $source->content_owner_id);
        $this->assertSame('source_register', data_get($source->metadata, 'module'));
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $source->getMorphClass(),
            'auditable_id' => $source->id,
            'event' => 'source_record.created',
        ]);

        $this->actingAs($staff)
            ->put(route('admin-console.source-records.update', $source), [
                ...$this->validSourcePayload(),
                'title' => 'Updated scholarship memo',
            ])
            ->assertRedirect(route('admin-console.source-records.show', $source));

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $source->getMorphClass(),
            'auditable_id' => $source->id,
            'event' => 'source_record.updated',
        ]);
    }

    public function test_source_record_archive_and_restore_are_policy_and_audit_controlled(): void
    {
        $superAdmin = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $source = SourceRecord::factory()->create();

        $this->actingAs($contentAdmin)
            ->post(route('admin-console.source-records.archive', $source))
            ->assertRedirect();

        $this->assertSoftDeleted($source);
        $this->assertDatabaseHas('audit_logs', ['event' => 'source_record.archived']);

        $this->actingAs($contentAdmin)
            ->post(route('admin-console.source-records.restore', $source))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->post(route('admin-console.source-records.restore', $source))
            ->assertRedirect();

        $this->assertNotSoftDeleted($source->fresh());
        $this->assertDatabaseHas('audit_logs', ['event' => 'source_record.restored']);
    }

    public function test_reference_data_manager_validates_persists_source_and_usage_warning(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.reference-data.store', ['type' => 'program-categories']), [
                'name' => '',
                'slug' => 'Not Valid',
                'sort_order' => -1,
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['name', 'slug', 'sort_order']);

        $this->actingAs($staff)
            ->post(route('admin-console.reference-data.store', ['type' => 'program-categories']), $this->validReferencePayload())
            ->assertRedirect();

        $category = ProgramCategory::query()->where('slug', 'student-services')->firstOrFail();

        $this->assertTrue($category->is_active);
        $this->assertDatabaseHas('source_records', [
            'sourceable_type' => $category->getMorphClass(),
            'sourceable_id' => $category->id,
            'title' => 'Reference taxonomy approval',
            'verified_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $category->getMorphClass(),
            'auditable_id' => $category->id,
            'event' => 'reference_data.created',
        ]);

        Program::factory()->for($category, 'category')->create(['title' => 'Linked program']);

        $this->actingAs($staff)
            ->put(route('admin-console.reference-data.update', [
                'type' => 'program-categories',
                'record' => $category->id,
            ]), [
                ...$this->validReferencePayload(),
                'is_active' => false,
            ])
            ->assertRedirect(route('admin-console.reference-data.show', [
                'type' => 'program-categories',
                'record' => $category->id,
            ]));

        $category->refresh();
        $this->assertFalse($category->is_active);

        $this->actingAs($staff)
            ->get(route('admin-console.reference-data.show', [
                'type' => 'program-categories',
                'record' => $category->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ReferenceData/Show')
                ->where('record.usage.count', 1)
                ->where('record.isActive', false));
    }

    public function test_reference_data_is_type_policy_protected_and_searchable(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        ProgramCategory::factory()->create(['name' => 'Livelihood', 'slug' => 'livelihood']);
        $source = SourceRecord::factory()->create(['title' => 'Searchable source']);

        $this->actingAs($staff)
            ->get(route('admin-console.reference-data.index', [
                'type' => 'program-categories',
                'search' => 'Livelihood',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ReferenceData/Index')
                ->where('definition.type', 'program-categories')
                ->where('records.data.0.title', 'Livelihood')
                ->where('records.meta.total', 1));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.reference-data.index', ['type' => 'program-categories']))
            ->assertForbidden();

        $this->actingAs($staff)
            ->getJson(route('admin-console.search', ['term' => 'Searchable']))
            ->assertOk()
            ->assertJsonPath('data.0.type', 'Source')
            ->assertJsonPath('data.0.href', route('admin-console.source-records.show', $source));
    }

    /**
     * @return array<string, mixed>
     */
    private function validSourcePayload(): array
    {
        return [
            'title' => 'Official scholarship memo',
            'url' => 'https://example.test/official-scholarship-memo',
            'source_type' => SourceType::PrimaryOfficial->value,
            'source_tier' => 'tier_1',
            'public_status' => 'published',
            'description' => 'Approved public source for scholarship guidance.',
            'content_owner_id' => null,
            'verified_by_id' => null,
            'verified_at' => now()->toDateString(),
            'last_reviewed_at' => now()->toDateString(),
            'next_review_at' => now()->addMonth()->toDateString(),
            'effective_at' => now()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
            'internal_notes' => 'No private documents attached.',
            'metadata_json' => '{"module":"source_register"}',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validReferencePayload(): array
    {
        return [
            'name' => 'Student Services',
            'slug' => 'student-services',
            'description' => 'Approved category for student-related program guidance.',
            'sort_order' => 2,
            'is_active' => true,
            'source_record' => [
                'title' => 'Reference taxonomy approval',
                'url' => 'https://example.test/reference-approval',
                'source_type' => SourceType::InternalApproval->value,
                'source_tier' => 'tier_1',
                'public_status' => 'published',
                'description' => 'Internal approval for taxonomy wording.',
                'verified_at' => now()->toDateString(),
                'next_review_at' => now()->addMonth()->toDateString(),
            ],
        ];
    }
}

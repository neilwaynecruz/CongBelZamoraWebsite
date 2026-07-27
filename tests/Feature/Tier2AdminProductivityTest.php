<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\ProgramCategoryResource;
use App\Filament\Resources\SourceRecordResource;
use App\Models\AuditLog;
use App\Models\ContentApproval;
use App\Models\ProgramCategory;
use App\Models\PublicPage;
use App\Models\SourceRecord;
use App\Models\User;
use App\Support\AdminContentInventoryExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Tier2AdminProductivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_record_resource_is_available_to_staff_with_due_filters(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        SourceRecord::factory()->create([
            'title' => 'Due official source',
            'next_review_at' => now()->subDay(),
            'content_owner_id' => $staff->id,
        ]);

        $this->assertSame('Content Governance', SourceRecordResource::getNavigationGroup());

        $this->actingAs($staff)
            ->get(SourceRecordResource::getUrl('index'))
            ->assertOk();
    }

    public function test_reference_data_resource_is_available_for_program_categories(): void
    {
        $admin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        ProgramCategory::factory()->create([
            'name' => 'Livelihood',
            'slug' => 'livelihood',
        ]);

        $this->assertSame('Reference Data', ProgramCategoryResource::getNavigationGroup());

        $this->actingAs($admin)
            ->get(ProgramCategoryResource::getUrl('index'))
            ->assertOk();
    }

    public function test_content_inventory_export_lists_public_content_without_props_payload(): void
    {
        PublicPage::factory()->create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy',
            'props' => ['sensitive_internal_shape' => 'do-not-export'],
        ]);

        $response = app(AdminContentInventoryExport::class)->stream();

        ob_start();
        $response->sendContent();
        $csv = (string) ob_get_clean();

        $this->assertStringContainsString('content-inventory', $response->headers->get('content-disposition') ?? '');
        $this->assertStringContainsString('Privacy Policy', $csv);
        $this->assertStringContainsString('Public page', $csv);
        $this->assertStringNotContainsString('sensitive_internal_shape', $csv);
    }

    public function test_audit_summary_export_omits_value_payloads(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();

        AuditLog::query()->create([
            'actor_user_id' => $auditor->id,
            'event' => 'updated',
            'old_values' => ['secret' => 'old'],
            'new_values' => ['secret' => 'new'],
            'metadata' => ['context' => 'hidden'],
            'occurred_at' => now(),
        ]);

        $response = AuditLogResource::exportAuditSummaryCsv(AuditLog::query()->with('actor'));

        ob_start();
        $response->sendContent();
        $csv = (string) ob_get_clean();

        $this->assertStringContainsString('event,actor,record_type,record_id,occurred_at', $csv);
        $this->assertStringContainsString('updated', $csv);
        $this->assertStringNotContainsString('secret', $csv);
        $this->assertStringNotContainsString('hidden', $csv);
    }

    public function test_scheduled_publish_promotes_due_approval_and_parent_content(): void
    {
        $page = PublicPage::factory()->create([
            'published_at' => null,
        ]);

        ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'status' => ContentApprovalStatus::Scheduled,
            'scheduled_for' => now()->subMinute(),
            'approved_at' => now()->subHour(),
        ]);

        Artisan::call('content:publish-scheduled');

        $this->assertSame(ContentApprovalStatus::Published, ContentApproval::query()->first()?->status);
        $this->assertNotNull($page->fresh()?->published_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'content.scheduled_published',
        ]);
    }
}

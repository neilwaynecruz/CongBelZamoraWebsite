<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\InquiryStatus;
use App\Filament\Widgets\DashboardHeaderWidget;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\PublicPage;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminDashboardMetricsService;
use App\Support\PublicPageProps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_accessible_to_super_administrators_as_emergency_fallback(): void
    {
        $user = User::factory()->create([
            'admin_role' => AdminRole::SuperAdministrator->value,
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertOk();
    }

    public function test_filament_dashboard_header_marks_panel_as_emergency_fallback(): void
    {
        $user = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        Livewire::actingAs($user)
            ->test(DashboardHeaderWidget::class)
            ->assertSee('Emergency fallback only.')
            ->assertSee('/admin-console');
    }

    public function test_non_super_staff_cannot_access_filament_fallback_but_can_use_admin_console(): void
    {
        $user = User::factory()->create([
            'admin_role' => AdminRole::ContentAdministrator->value,
        ]);

        $this->assertFalse($user->canAccessPanel(filament()->getDefaultPanel()));

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin-console.dashboard'))
            ->assertOk();
    }

    public function test_legacy_admin_fallback_navigation_is_super_administrator_only(): void
    {
        $super = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $editor = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $superLabels = collect(app(AdminConsoleNavigationService::class)->for($super))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $editorLabels = collect(app(AdminConsoleNavigationService::class)->for($editor))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($superLabels->contains('Legacy Admin Fallback'));
        $this->assertFalse($editorLabels->contains('Legacy Admin Fallback'));
    }

    public function test_guest_and_inactive_staff_do_not_reach_filament_fallback(): void
    {
        $inactive = User::factory()->adminRole(AdminRole::SuperAdministrator)->create([
            'is_active' => false,
        ]);

        $this->get('/admin')
            ->assertRedirect('/admin/login');

        $this->actingAs($inactive)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($inactive)
            ->get(route('admin-console.dashboard'))
            ->assertForbidden();
    }

    public function test_unverified_super_administrator_cannot_access_filament_fallback(): void
    {
        $user = User::factory()
            ->unverified()
            ->adminRole(AdminRole::SuperAdministrator)
            ->create();

        $this->assertFalse($user->canAccessPanel(filament()->getDefaultPanel()));

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_metrics_service_returns_accurate_counts(): void
    {
        $service = app(AdminDashboardMetricsService::class);
        $user = User::factory()->create([
            'admin_role' => AdminRole::SuperAdministrator->value,
        ]);

        $counts = $service->getCachedCounts();
        $this->assertArrayHasKey('open_programs', $counts);
        $this->assertArrayHasKey('pending_approvals', $counts);
        $this->assertArrayHasKey('stale_content', $counts);
        $this->assertArrayHasKey('sources_due', $counts);

        $inquiryCount = $service->getUnansweredInquiriesCount($user);
        $this->assertEquals(0, $inquiryCount);
    }

    public function test_metrics_service_inquiry_counts_are_role_gated(): void
    {
        $service = app(AdminDashboardMetricsService::class);

        $super = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator->value]);
        $officer = User::factory()->create(['admin_role' => AdminRole::InquiryOfficer->value]);
        $editor = User::factory()->create(['admin_role' => AdminRole::CommunicationsEditor->value]);

        $this->assertGreaterThanOrEqual(0, $service->getUnansweredInquiriesCount($super));
        $this->assertGreaterThanOrEqual(0, $service->getUnansweredInquiriesCount($officer));
        $this->assertEquals(0, $service->getUnansweredInquiriesCount($editor));
    }

    public function test_needs_attention_items_are_role_gated(): void
    {
        $service = app(AdminDashboardMetricsService::class);

        $super = User::factory()->create(['admin_role' => AdminRole::SuperAdministrator->value]);
        $editor = User::factory()->create(['admin_role' => AdminRole::CommunicationsEditor->value]);

        Inquiry::factory()->create([
            'reference_code' => 'INQ-2026-PRIVATE',
            'status' => InquiryStatus::Pending,
            'created_at' => now()->subHours(49),
        ]);

        $superItems = $service->getNeedsAttentionItems($super);
        $editorItems = $service->getNeedsAttentionItems($editor);

        $superInquiries = collect($superItems)->filter(fn ($item) => $item['type'] === 'Inquiry');
        $editorInquiries = collect($editorItems)->filter(fn ($item) => $item['type'] === 'Inquiry');

        $this->assertNotEmpty($superInquiries);
        $this->assertCount(0, $editorInquiries);
    }

    public function test_needs_attention_inquiry_items_do_not_expose_citizen_details(): void
    {
        $service = app(AdminDashboardMetricsService::class);
        $officer = User::factory()->create(['admin_role' => AdminRole::InquiryOfficer->value]);

        Inquiry::factory()->create([
            'reference_code' => 'INQ-2026-REDACT',
            'citizen_name' => 'Sensitive Citizen Name',
            'message_body' => 'Sensitive message body should stay off the dashboard.',
            'status' => InquiryStatus::Pending,
            'created_at' => now()->subHours(49),
        ]);

        $items = $service->getNeedsAttentionItems($officer);
        $encodedItems = json_encode($items, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('INQ-2026-REDACT', $encodedItems);
        $this->assertStringNotContainsString('Sensitive Citizen Name', $encodedItems);
        $this->assertStringNotContainsString('Sensitive message body', $encodedItems);
    }

    public function test_dashboard_health_check_is_audited(): void
    {
        $user = User::factory()->create([
            'admin_role' => AdminRole::ReviewerApprover->value,
        ]);

        foreach (PublicPageProps::requiredSlugs() as $slug) {
            PublicPage::factory()->create([
                'slug' => $slug,
                'published_at' => now(),
            ]);
        }

        Livewire::actingAs($user)
            ->test(DashboardHeaderWidget::class)
            ->call('checkRequiredPublicPages');

        $this->assertDatabaseHas(AuditLog::class, [
            'actor_user_id' => $user->id,
            'event' => 'admin_dashboard_health_check_run',
        ]);
    }
}

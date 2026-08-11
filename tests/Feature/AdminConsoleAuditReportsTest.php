<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryCategory;
use App\Models\AuditLog;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleAuditReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_audit_and_reports_navigation_is_role_aware(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        AuditLog::factory()->create(['event' => 'content.updated']);

        $this->actingAs($auditor)
            ->get(route('admin-console.audit.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Audit/Index')
                ->where('adminConsole.user.role', AdminRole::ReadOnlyAuditor->value));

        $this->actingAs($auditor)
            ->get(route('admin-console.reports'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Index')
                ->where('reports.exports.enabled', false));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($auditor))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('Audit Trail'));
        $this->assertTrue($labels->contains('Reports'));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.audit.index'))
            ->assertForbidden();

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.reports'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Index')
                ->where('reports.can.viewInquiryReports', true)
                ->where('reports.can.viewPublishReports', false));
    }

    public function test_audit_index_filters_by_event_type_actor_system_and_date(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $actor = User::factory()->adminRole(AdminRole::ContentAdministrator)->create([
            'name' => 'Content Lead',
        ]);
        $program = Program::factory()->create(['title' => 'Filtered Audit Program']);

        AuditLog::factory()->create([
            'actor_user_id' => $actor->id,
            'auditable_type' => $program->getMorphClass(),
            'auditable_id' => $program->id,
            'event' => 'program.updated',
            'occurred_at' => now()->subDay(),
        ]);
        AuditLog::factory()->create([
            'actor_user_id' => null,
            'event' => 'content.review_overdue',
            'occurred_at' => now(),
        ]);

        $this->actingAs($auditor)
            ->get(route('admin-console.audit.index', [
                'event' => 'program.updated',
                'type' => $program->getMorphClass(),
                'actor' => $actor->id,
                'date_from' => now()->subDays(2)->toDateString(),
                'date_until' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Audit/Index')
                ->where('auditLogs.data.0.event', 'program.updated')
                ->where('auditLogs.data.0.actor.name', 'Content Lead')
                ->where('auditLogs.data.0.auditable.title', 'Filtered Audit Program')
                ->where('auditLogs.meta.total', 1));

        $this->actingAs($auditor)
            ->get(route('admin-console.audit.index', ['actor' => 'system']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('auditLogs.data.0.event', 'content.review_overdue')
                ->where('auditLogs.data.0.actor.name', 'System')
                ->where('auditLogs.meta.total', 1));
    }

    public function test_audit_detail_redacts_sensitive_values_and_exposes_hashes(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $actor = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $program = Program::factory()->create(['title' => 'Sensitive Audit Target']);

        $auditLog = AuditLog::factory()->create([
            'actor_user_id' => $actor->id,
            'auditable_type' => $program->getMorphClass(),
            'auditable_id' => $program->id,
            'event' => 'staff_user.updated',
            'ip_address_hash' => str_repeat('a', 64),
            'user_agent_hash' => str_repeat('b', 64),
            'old_values' => [
                'email' => 'old.staff@example.test',
                'password' => 'raw-old-password',
                'citizen_email' => 'resident@example.test',
            ],
            'new_values' => [
                'email' => 'new.staff@example.test',
                'password' => 'raw-new-password',
                'two_factor_secret' => 'secret-value',
                'message_body' => 'Private inquiry message',
            ],
            'metadata' => [
                'attachment_path' => 'private/path/file.pdf',
                'custom_admin_console' => true,
            ],
        ]);

        $response = $this->actingAs($auditor)
            ->get(route('admin-console.audit.show', $auditLog))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Audit/Show')
                ->where('auditLog.event', 'staff_user.updated')
                ->where('auditLog.auditable.title', 'Sensitive Audit Target')
                ->where('auditLog.network.ipAddressHash', str_repeat('a', 64))
                ->where('auditLog.oldValues.password', '[REDACTED]')
                ->where('auditLog.oldValues.citizen_email', '[REDACTED]')
                ->where('auditLog.newValues.password', '[REDACTED]')
                ->where('auditLog.newValues.two_factor_secret', '[REDACTED]')
                ->where('auditLog.newValues.message_body', '[REDACTED]')
                ->where('auditLog.metadata.attachment_path', '[REDACTED]'));

        $response->assertDontSee('raw-new-password');
        $response->assertDontSee('secret-value');
        $response->assertDontSee('Private inquiry message');
        $response->assertDontSee('private/path/file.pdf');
    }

    public function test_reports_dashboard_uses_real_aggregate_metrics_without_private_inquiry_data(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();

        Inquiry::factory()->count(2)->create([
            'inquiry_category' => InquiryCategory::EducationalAssistance,
            'citizen_name' => 'Do Not Expose',
            'message_body' => 'Private inquiry body',
        ]);
        Inquiry::factory()->create([
            'inquiry_category' => InquiryCategory::GeneralInquiry,
        ]);

        $page = PublicPage::factory()->create([
            'published_at' => now(),
            'review_due_at' => now()->subDay(),
        ]);
        ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'status' => ContentApprovalStatus::Published,
            'submitted_at' => now()->subHours(10),
            'published_at' => now()->subHours(2),
        ]);
        Project::factory()->create(['barangay' => 'Greenhills']);

        $response = $this->actingAs($auditor)
            ->get(route('admin-console.reports', ['days' => 30]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Index')
                ->where('reports.period.days', 30)
                ->where('reports.can.viewInquiryReports', true)
                ->where('reports.can.viewPublishReports', true)
                ->where('reports.can.viewStaleReports', true)
                ->where('reports.inquiries.summary.total', 3)
                ->where('reports.publishing.latency.sample_size', 1)
                ->where('reports.exports.enabled', false));

        $response->assertDontSee('Do Not Expose');
        $response->assertDontSee('Private inquiry body');
    }

    public function test_reports_reject_unapproved_period_values(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();

        $this->actingAs($auditor)
            ->get(route('admin-console.reports', ['days' => 365]))
            ->assertSessionHasErrors('days');
    }
}

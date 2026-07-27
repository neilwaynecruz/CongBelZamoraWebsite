<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryCategory;
use App\Events\ContentApprovalDecided;
use App\Events\ContentApprovalSubmitted;
use App\Events\InquiryAssigned;
use App\Http\Middleware\EnsureStaffTwoFactorIsEnabled;
use App\Jobs\FlagStaleContent;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\User;
use App\Notifications\ContentApprovalDecisionNotification;
use App\Notifications\ContentApprovalSubmittedNotification;
use App\Notifications\InquiryAssignedNotification;
use App\Notifications\StaffInquiryReceivedNotification;
use App\Notifications\StaleContentDigestNotification;
use App\Services\AdminReportsMetricsService;
use App\Services\SubmitInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Tier4AdminEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_inquiry_notification_uses_database_channel_without_pii(): void
    {
        Notification::fake();

        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        $request = Request::create('/contact/inquiries', 'POST', [
            'citizen_name' => 'Sensitive Citizen',
            'contact_preference' => 'email',
            'citizen_email' => 'citizen@example.com',
            'inquiry_category' => InquiryCategory::GeneralInquiry->value,
            'message_body' => 'Sensitive private message body',
            'privacy_consent' => true,
        ]);

        $inquiry = app(SubmitInquiry::class)->handle([
            'citizen_name' => 'Sensitive Citizen',
            'contact_preference' => 'email',
            'citizen_email' => 'citizen@example.com',
            'inquiry_category' => InquiryCategory::GeneralInquiry->value,
            'message_body' => 'Sensitive private message body',
        ], $request);

        Notification::assertSentTo(
            $officer,
            StaffInquiryReceivedNotification::class,
            function (StaffInquiryReceivedNotification $notification, array $channels) use ($inquiry, $officer): bool {
                $this->assertContains('database', $channels);
                $this->assertContains('mail', $channels);

                $payload = $notification->toArray($officer);
                $encoded = json_encode($payload, JSON_THROW_ON_ERROR);

                $this->assertSame($inquiry->reference_code, $payload['reference_code']);
                $this->assertStringNotContainsString('Sensitive Citizen', $encoded);
                $this->assertStringNotContainsString('Sensitive private message body', $encoded);
                $this->assertStringNotContainsString('citizen@example.com', $encoded);

                return true;
            },
        );
    }

    public function test_inquiry_assignment_dispatches_notification_to_assignee(): void
    {
        Notification::fake();

        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => null,
        ]);

        InquiryAssigned::dispatch($inquiry, $officer);

        Notification::assertSentTo($officer, InquiryAssignedNotification::class);
    }

    public function test_content_approval_events_notify_reviewers_and_submitter(): void
    {
        Notification::fake();

        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $submitter = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $page = PublicPage::factory()->create();
        $approval = ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'submitted_by_id' => $submitter->id,
            'status' => ContentApprovalStatus::ForReview,
        ]);

        ContentApprovalSubmitted::dispatch($approval, $submitter);
        Notification::assertSentTo($reviewer, ContentApprovalSubmittedNotification::class);
        Notification::assertNotSentTo($submitter, ContentApprovalSubmittedNotification::class);

        ContentApprovalDecided::dispatch($approval, ContentApprovalStatus::Approved, $reviewer);
        Notification::assertSentTo($submitter, ContentApprovalDecisionNotification::class);
    }

    public function test_stale_content_digest_includes_database_channel(): void
    {
        Notification::fake();

        $admin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        PublicPage::factory()->create([
            'review_due_at' => now()->subDay(),
        ]);

        (new FlagStaleContent)->handle(app(\App\Services\AuditLogService::class));

        Notification::assertSentTo(
            $admin,
            StaleContentDigestNotification::class,
            function (StaleContentDigestNotification $notification, array $channels) use ($admin): bool {
                $this->assertContains('database', $channels);
                $payload = $notification->toArray($admin);
                $this->assertArrayHasKey('counts', $payload);
                $this->assertArrayHasKey('title', $payload);

                return true;
            },
        );
    }

    public function test_reports_metrics_are_role_gated_and_aggregate_only(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $editor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $projectEditor = User::factory()->adminRole(AdminRole::ProjectEditor)->create();

        Inquiry::factory()->count(2)->create([
            'inquiry_category' => InquiryCategory::EducationalAssistance,
            'citizen_name' => 'Should Not Appear',
            'message_body' => 'Private body',
        ]);
        Inquiry::factory()->create([
            'inquiry_category' => InquiryCategory::GeneralInquiry,
        ]);

        $page = PublicPage::factory()->create();
        ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'status' => ContentApprovalStatus::Published,
            'submitted_at' => now()->subHours(10),
            'published_at' => now()->subHours(2),
        ]);

        Project::factory()->create([
            'barangay' => 'Greenhills',
        ]);

        $service = app(AdminReportsMetricsService::class);

        $volume = $service->getInquiryVolumeByCategory($officer);
        $this->assertNotEmpty($volume);
        $encoded = json_encode($volume, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('Should Not Appear', $encoded);
        $this->assertStringNotContainsString('Private body', $encoded);
        $this->assertSame([], $service->getInquiryVolumeByCategory($editor));

        $latency = $service->getPublishLatencyStats($reviewer);
        $this->assertSame(1, $latency['sample_size']);
        $this->assertNotNull($latency['average_hours']);
        $this->assertSame(0, $service->getPublishLatencyStats($officer)['sample_size']);

        $stale = $service->getStaleContentBreakdown($reviewer);
        $this->assertNotEmpty($stale);
        $this->assertSame([], $service->getStaleContentBreakdown($officer));

        $projects = $service->getProjectStatsByBarangay($projectEditor);
        $this->assertTrue(collect($projects)->contains(
            fn (array $row): bool => $row['barangay'] === 'Greenhills' && $row['total'] >= 1,
        ));
    }

    public function test_staff_without_2fa_are_redirected_when_enforcement_enabled(): void
    {
        Config::set('belzamora.staff.require_2fa', true);

        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $this->actingAs($staff)
            ->get('/admin')
            ->assertRedirect(route('security.edit'));

        $enabled = User::factory()->adminRole(AdminRole::ContentAdministrator)->withTwoFactor()->create();

        $this->actingAs($enabled)
            ->get('/admin')
            ->assertOk();
    }

    public function test_launch_readiness_fails_when_active_staff_missing_2fa(): void
    {
        Config::set('app.env', 'production');
        Config::set('app.debug', false);
        Config::set('app.url', 'https://www.belzamora.com');
        Config::set('mail.default', 'smtp');
        Config::set('queue.default', 'redis');
        Config::set('cache.default', 'redis');
        Config::set('belzamora.inquiries.staff_notification_email', 'staff@example.com');
        Config::set('belzamora.staff.require_2fa', true);

        foreach (\App\Support\PublicPageProps::requiredSlugs() as $slug) {
            PublicPage::factory()->create([
                'slug' => $slug,
                'published_at' => now(),
            ]);
        }

        ContentApproval::factory()->create([
            'status' => ContentApprovalStatus::Approved,
            'metadata' => ['launch_gate' => true, 'launch_item' => 'Test gate'],
        ]);

        User::factory()->adminRole(AdminRole::SuperAdministrator)->create([
            'email' => 'missing-2fa@example.com',
        ]);

        $this->artisan('launch:readiness', ['--production' => true])
            ->expectsOutputToContain('Active staff missing confirmed 2FA')
            ->assertFailed();
    }

    public function test_two_factor_middleware_allows_access_when_requirement_disabled(): void
    {
        Config::set('belzamora.staff.require_2fa', false);

        $middleware = new EnsureStaffTwoFactorIsEnabled;
        $staff = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        $request = Request::create('/admin', 'GET');
        $request->setUserResolver(fn () => $staff);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }
}

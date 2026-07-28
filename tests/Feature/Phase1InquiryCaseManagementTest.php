<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\InquiryActivityType;
use App\Enums\InquiryCategory;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Jobs\SendOverdueInquiryReminders;
use App\Models\Inquiry;
use App\Models\InquiryActivity;
use App\Models\PublicPage;
use App\Models\User;
use App\Notifications\OverdueInquiryReminderNotification;
use App\Services\AdminDashboardMetricsService;
use App\Services\AdminReportsMetricsService;
use App\Services\InquiryCaseManagementService;
use App\Services\SubmitInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase1InquiryCaseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_management_schema_is_available(): void
    {
        foreach ([
            'priority',
            'office_unit',
            'assigned_at',
            'first_response_due_at',
            'first_responded_at',
            'last_internal_activity_at',
            'closed_at',
            'closed_reason',
            'overdue_reminder_sent_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('inquiries', $column));
        }

        $this->assertTrue(Schema::hasTable('inquiry_activities'));
    }

    public function test_public_submission_is_routed_and_creates_case_timeline_without_plain_pii(): void
    {
        Notification::fake();

        $request = Request::create('/contact/inquiries', 'POST', [
            'citizen_name' => 'Juan Dela Cruz',
            'contact_preference' => 'email',
            'citizen_email' => 'juan@example.com',
            'inquiry_category' => InquiryCategory::EducationalAssistance->value,
            'message_body' => 'I want to ask about current scholarship requirements.',
            'privacy_consent' => true,
        ]);

        $inquiry = app(SubmitInquiry::class)->handle([
            'citizen_name' => 'Juan Dela Cruz',
            'contact_preference' => 'email',
            'citizen_email' => 'juan@example.com',
            'inquiry_category' => InquiryCategory::EducationalAssistance->value,
            'message_body' => 'I want to ask about current scholarship requirements.',
        ], $request);

        $inquiry = $inquiry->fresh(['activities']);

        $this->assertSame(InquiryPriority::High, $inquiry?->priority);
        $this->assertSame('Education and Scholarship Assistance Desk', $inquiry?->office_unit);
        $this->assertNotNull($inquiry?->first_response_due_at);
        $this->assertSame('education_program', data_get($inquiry?->metadata, 'acknowledgment_variant'));
        $this->assertSame(2, $inquiry?->activities()->count());
        $this->assertDatabaseHas('inquiry_activities', [
            'inquiry_id' => $inquiry?->id,
            'type' => InquiryActivityType::Routed->value,
        ]);

        $rawActivity = (string) InquiryActivity::query()->where('inquiry_id', $inquiry?->id)->first()?->getRawOriginal('body');
        $this->assertStringNotContainsString('Juan Dela Cruz', $rawActivity);
        $this->assertStringNotContainsString('scholarship requirements', $rawActivity);
    }

    public function test_case_service_records_assignment_response_resolution_closure_and_reopen_activity(): void
    {
        Notification::fake();

        $actor = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $assignee = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => null,
            'status' => InquiryStatus::Pending,
        ]);

        $service = app(InquiryCaseManagementService::class);

        $service->assign($inquiry, $assignee, $actor, 'Assigning to education desk.');
        $service->markResponded($inquiry->fresh(), $assignee, 'Response sent by approved office channel.');
        $service->resolve($inquiry->fresh(), $assignee, 'Guidance provided.');
        $service->close($inquiry->fresh(), $assignee, 'resolved_guidance_given', 'Closing after resolution.');
        $service->reopen($inquiry->fresh(), $assignee, 'Follow-up received.');

        $fresh = $inquiry->fresh();

        $this->assertSame($assignee->id, $fresh?->assigned_officer_id);
        $this->assertNotNull($fresh?->assigned_at);
        $this->assertNotNull($fresh?->first_responded_at);
        $this->assertNull($fresh?->closed_at);
        $this->assertNull($fresh?->closed_reason);
        $this->assertSame(InquiryStatus::UnderReview, $fresh?->status);
        $this->assertGreaterThanOrEqual(5, $fresh?->activities()->count());
    }

    public function test_overdue_reminder_job_notifies_assignee_and_marks_timeline(): void
    {
        Notification::fake();

        $assignee = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->overdue()->create([
            'assigned_officer_id' => $assignee->id,
            'assigned_at' => now()->subDays(2),
            'overdue_reminder_sent_at' => null,
        ]);

        (new SendOverdueInquiryReminders)->handle(app(InquiryCaseManagementService::class));

        Notification::assertSentTo($assignee, OverdueInquiryReminderNotification::class);

        $this->assertNotNull($inquiry->fresh()?->overdue_reminder_sent_at);
        $this->assertDatabaseHas('inquiry_activities', [
            'inquiry_id' => $inquiry->id,
            'type' => InquiryActivityType::OverdueReminder->value,
        ]);
    }

    public function test_dashboard_and_report_metrics_include_case_management_dimensions(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $other = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        Inquiry::factory()->overdue()->create([
            'assigned_officer_id' => $officer->id,
            'assigned_at' => now()->subDays(2),
        ]);
        Inquiry::factory()->create([
            'assigned_officer_id' => null,
            'status' => InquiryStatus::Pending,
        ]);
        Inquiry::factory()->create([
            'assigned_officer_id' => $other->id,
            'status' => InquiryStatus::Resolved,
            'responded_at' => now()->subHours(4),
            'first_responded_at' => now()->subHours(4),
            'resolved_at' => now(),
        ]);

        $queue = app(AdminDashboardMetricsService::class)->getInquiryQueueCounts($officer);
        $metrics = app(AdminReportsMetricsService::class)->getInquiryCaseMetrics($officer);

        $this->assertSame(1, $queue['assigned_open']);
        $this->assertSame(1, $queue['assigned_overdue']);
        $this->assertSame(1, $queue['unassigned_open']);
        $this->assertNotEmpty($metrics['by_status']);
        $this->assertNotEmpty($metrics['by_assignee']);
        $this->assertNotEmpty($metrics['response_bands']);
    }

    public function test_contact_page_receives_case_workflow_guidance(): void
    {
        PublicPage::factory()->create([
            'slug' => 'contact',
            'component' => 'Public/Contact',
            'published_at' => now(),
            'props' => [
                'breadcrumbs' => [],
                'office' => [],
                'channels' => [],
                'privacyHighlights' => [],
                'responseExpectations' => [],
                'verifiedAt' => 'July 28, 2026',
            ],
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Contact')
                ->where('caseWorkflow.expectedResponse', config('belzamora.inquiries.public_workflow.expected_response'))
                ->has('caseWorkflow.afterSubmission', 4));
    }
}

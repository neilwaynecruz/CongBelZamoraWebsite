<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\InquiryActivityType;
use App\Enums\InquiryStatus;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\InquiryCaseManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AdminConsoleInquiryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('belzamora.staff.require_2fa', false);
    }

    public function test_authorized_officer_can_complete_the_audited_case_lifecycle(): void
    {
        Notification::fake();

        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'status' => InquiryStatus::Pending,
            'assigned_officer_id' => null,
        ]);

        $this->actingAs($officer)
            ->post(route('admin-console.inquiries.assign', $inquiry), [
                'assigned_officer_id' => $officer->id,
                'note' => 'Accepted by the assigned service desk.',
            ])
            ->assertRedirect(route('admin-console.inquiries.show', $inquiry))
            ->assertInertiaFlash('toast.message', 'Inquiry ownership updated.');

        $this->assertSame(InquiryStatus::UnderReview, $inquiry->fresh()?->status);
        $this->assertSame($officer->id, $inquiry->fresh()?->assigned_officer_id);

        $internalNote = 'Confirmed routing context with the responsible office unit.';

        $this->actingAs($officer)
            ->post(route('admin-console.inquiries.notes.store', $inquiry), [
                'template_key' => 'ack_received',
                'note' => $internalNote,
            ])
            ->assertRedirect(route('admin-console.inquiries.show', $inquiry));

        $responseSummary = 'Response delivered through the approved office phone channel.';

        $this->actingAs($officer)
            ->post(route('admin-console.inquiries.responses.store', $inquiry), [
                'template_key' => 'program_guidance',
                'note' => $responseSummary,
            ])
            ->assertRedirect(route('admin-console.inquiries.show', $inquiry));

        $this->actingAs($officer)
            ->post(route('admin-console.inquiries.resolve', $inquiry), [
                'note' => 'The requested guidance and official source pathway were provided.',
            ])
            ->assertRedirect(route('admin-console.inquiries.show', $inquiry));

        $this->actingAs($officer)
            ->post(route('admin-console.inquiries.close', $inquiry), [
                'closed_reason' => 'resolved_guidance_given',
                'note' => 'Case completed after the documented response.',
            ])
            ->assertRedirect(route('admin-console.inquiries.show', $inquiry));

        $this->actingAs($officer)
            ->post(route('admin-console.inquiries.reopen', $inquiry), [
                'note' => 'A relevant follow-up requires another review cycle.',
            ])
            ->assertRedirect(route('admin-console.inquiries.show', $inquiry));

        $fresh = $inquiry->fresh();

        $this->assertSame(InquiryStatus::UnderReview, $fresh?->status);
        $this->assertSame($officer->id, $fresh?->assigned_officer_id);
        $this->assertNotNull($fresh?->first_responded_at);
        $this->assertNull($fresh?->resolved_at);
        $this->assertNull($fresh?->closed_at);
        $this->assertNull($fresh?->closed_reason);

        foreach ([
            InquiryActivityType::Assigned,
            InquiryActivityType::InternalNote,
            InquiryActivityType::StaffResponse,
            InquiryActivityType::Resolved,
            InquiryActivityType::Closed,
            InquiryActivityType::Reopened,
        ] as $activityType) {
            $this->assertDatabaseHas('inquiry_activities', [
                'inquiry_id' => $inquiry->id,
                'type' => $activityType->value,
            ]);
        }

        foreach ([
            'inquiry.assigned',
            'inquiry.internal_note_added',
            'inquiry.responded',
            'inquiry.resolved',
            'inquiry.closed',
            'inquiry.reopened',
        ] as $event) {
            $this->assertDatabaseHas('audit_logs', [
                'auditable_type' => Inquiry::class,
                'auditable_id' => $inquiry->id,
                'event' => $event,
                'actor_user_id' => $officer->id,
            ]);
        }

        $assignmentAudit = AuditLog::query()
            ->where('auditable_type', Inquiry::class)
            ->where('auditable_id', $inquiry->id)
            ->where('event', 'inquiry.assigned')
            ->firstOrFail();

        $this->assertNull(data_get($assignmentAudit->old_values, 'assigned_officer_id'));
        $this->assertSame($officer->id, data_get($assignmentAudit->new_values, 'assigned_officer_id'));

        $rawNote = (string) DB::table('inquiry_activities')
            ->where('inquiry_id', $inquiry->id)
            ->where('type', InquiryActivityType::InternalNote->value)
            ->value('body');
        $auditPayload = AuditLog::query()
            ->where('auditable_type', Inquiry::class)
            ->where('auditable_id', $inquiry->id)
            ->get()
            ->flatMap(fn (AuditLog $log): array => [
                json_encode($log->old_values),
                json_encode($log->new_values),
                json_encode($log->metadata),
            ])
            ->implode(' ');

        $this->assertStringNotContainsString($internalNote, $rawNote);
        $this->assertStringNotContainsString($internalNote, $auditPayload);
        $this->assertStringNotContainsString($responseSummary, $auditPayload);
    }

    public function test_start_review_claims_an_unassigned_case_and_records_the_transition(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'status' => InquiryStatus::Pending,
            'assigned_officer_id' => null,
        ]);

        $this->actingAs($officer)
            ->post(route('admin-console.inquiries.mark-under-review', $inquiry), [
                'note' => 'Review started from the custom command system.',
            ])
            ->assertRedirect(route('admin-console.inquiries.show', $inquiry));

        $fresh = $inquiry->fresh();

        $this->assertSame(InquiryStatus::UnderReview, $fresh?->status);
        $this->assertSame($officer->id, $fresh?->assigned_officer_id);
        $this->assertNotNull($fresh?->assigned_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inquiry.under_review',
            'auditable_id' => $inquiry->id,
            'actor_user_id' => $officer->id,
        ]);
    }

    public function test_read_only_auditor_cannot_execute_any_case_command(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $assignee = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => $assignee->id,
            'status' => InquiryStatus::UnderReview,
        ]);

        $commands = [
            [route('admin-console.inquiries.assign', $inquiry), ['assigned_officer_id' => $assignee->id]],
            [route('admin-console.inquiries.mark-under-review', $inquiry), []],
            [route('admin-console.inquiries.notes.store', $inquiry), ['note' => 'Unauthorized note']],
            [route('admin-console.inquiries.responses.store', $inquiry), ['note' => 'Unauthorized response']],
            [route('admin-console.inquiries.resolve', $inquiry), ['note' => 'Unauthorized resolution']],
            [route('admin-console.inquiries.close', $inquiry), ['closed_reason' => 'resolved_guidance_given']],
            [route('admin-console.inquiries.reopen', $inquiry), ['note' => 'Unauthorized reopen']],
        ];

        foreach ($commands as [$url, $payload]) {
            $this->actingAs($auditor)->post($url, $payload)->assertForbidden();
        }

        $this->assertSame(0, $inquiry->activities()->count());
        $this->assertSame(InquiryStatus::UnderReview, $inquiry->fresh()?->status);
    }

    public function test_inquiry_officer_cannot_change_a_case_owned_by_another_officer(): void
    {
        $owner = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $otherOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => $owner->id,
            'status' => InquiryStatus::UnderReview,
        ]);

        $this->actingAs($otherOfficer)
            ->post(route('admin-console.inquiries.notes.store', $inquiry), [
                'note' => 'Attempted unauthorized case note.',
            ])
            ->assertForbidden();

        $this->assertSame(0, $inquiry->activities()->count());
    }

    public function test_assignment_accepts_only_active_authorized_inquiry_staff(): void
    {
        $administrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $inactiveOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create(['is_active' => false]);
        $contentEditor = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $inquiry = Inquiry::factory()->create();

        foreach ([$inactiveOfficer, $contentEditor] as $invalidAssignee) {
            $this->actingAs($administrator)
                ->from(route('admin-console.inquiries.show', $inquiry))
                ->post(route('admin-console.inquiries.assign', $inquiry), [
                    'assigned_officer_id' => $invalidAssignee->id,
                ])
                ->assertRedirect(route('admin-console.inquiries.show', $inquiry))
                ->assertSessionHasErrors('assigned_officer_id');
        }

        $this->assertNull($inquiry->fresh()?->assigned_officer_id);
        $this->assertSame(0, $inquiry->activities()->count());
    }

    public function test_response_and_resolution_require_accountable_ownership(): void
    {
        $administrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => null,
            'status' => InquiryStatus::Pending,
        ]);

        $this->actingAs($administrator)
            ->post(route('admin-console.inquiries.responses.store', $inquiry), [
                'note' => 'This should be blocked until ownership is assigned.',
            ])
            ->assertForbidden();

        $this->actingAs($administrator)
            ->post(route('admin-console.inquiries.resolve', $inquiry), [
                'note' => 'This should also be blocked until ownership is assigned.',
            ])
            ->assertForbidden();

        $this->assertNull($inquiry->fresh()?->first_responded_at);
        $this->assertSame(InquiryStatus::Pending, $inquiry->fresh()?->status);
    }

    public function test_dossier_returns_role_and_state_specific_workflow_controls(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $otherOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => $officer->id,
            'status' => InquiryStatus::UnderReview,
        ]);

        $this->actingAs($officer)
            ->get(route('admin-console.inquiries.show', $inquiry))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Inquiries/Show')
                ->where('workflow.writable', true)
                ->where('workflow.actions.assign.allowed', true)
                ->where('workflow.actions.recordResponse.allowed', true)
                ->where('workflow.actions.resolve.allowed', true)
                ->where('workflow.actions.close.allowed', false)
                ->has('workflow.assignees', 2)
                ->has('workflow.templates')
                ->has('workflow.closureReasons'));

        $this->actingAs($auditor)
            ->get(route('admin-console.inquiries.show', $inquiry))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('workflow.writable', false)
                ->where('workflow.actions.assign.allowed', false)
                ->where('workflow.actions.addInternalNote.allowed', false)
                ->has('workflow.assignees', 0)
                ->where('inquiry.citizen.restricted', true));
    }

    public function test_transaction_rolls_back_case_and_timeline_when_audit_persistence_fails(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => $officer->id,
            'internal_notes' => null,
        ]);
        $auditLog = Mockery::mock(AuditLogService::class);
        $auditLog->shouldReceive('record')->once()->andThrow(new RuntimeException('Audit store unavailable.'));
        $service = new InquiryCaseManagementService($auditLog);

        try {
            $service->addInternalNote($inquiry, $officer, 'This note must be rolled back.');
            $this->fail('The audit failure should abort the case transaction.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit store unavailable.', $exception->getMessage());
        }

        $this->assertNull($inquiry->fresh()?->internal_notes);
        $this->assertSame(0, $inquiry->activities()->count());
    }

    public function test_service_rejects_stale_or_invalid_state_transitions(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => $officer->id,
            'status' => InquiryStatus::Pending,
        ]);

        try {
            app(InquiryCaseManagementService::class)->close(
                $inquiry,
                $officer,
                'resolved_guidance_given',
            );
            $this->fail('Pending inquiries must not close directly.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Only resolved inquiries can be closed.',
                $exception->errors()['action'][0] ?? null,
            );
        }

        $this->assertSame(InquiryStatus::Pending, $inquiry->fresh()?->status);
        $this->assertSame(0, $inquiry->activities()->count());
        $this->assertDatabaseMissing('audit_logs', [
            'event' => 'inquiry.closed',
            'auditable_id' => $inquiry->id,
        ]);
    }
}

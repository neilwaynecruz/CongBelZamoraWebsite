<?php

namespace App\Services;

use App\Enums\InquiryActivityType;
use App\Enums\InquiryCategory;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Events\InquiryAssigned;
use App\Models\Inquiry;
use App\Models\InquiryActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InquiryCaseManagementService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function applyInitialRouting(Inquiry $inquiry): Inquiry
    {
        $route = $this->routeFor($inquiry->inquiry_category);
        $targetHours = max(1, (int) data_get($route, 'target_first_response_hours', config('belzamora.inquiries.unanswered_sla_hours', 48)));
        $routingNotes = is_array($inquiry->routing_notes) ? $inquiry->routing_notes : [];

        $routingNotes['category_route'] = [
            'office_unit' => (string) data_get($route, 'office_unit'),
            'priority' => (string) data_get($route, 'priority'),
            'target_first_response_hours' => $targetHours,
            'acknowledgment_variant' => (string) data_get($route, 'acknowledgment_variant', 'standard'),
            'note' => (string) data_get($route, 'routing_note'),
            'routed_at' => now('Asia/Manila')->toIso8601String(),
        ];

        $inquiry->forceFill([
            'office_unit' => (string) data_get($route, 'office_unit', config('belzamora.inquiries.case_management.default_office_unit')),
            'priority' => (string) data_get($route, 'priority', config('belzamora.inquiries.case_management.default_priority')),
            'first_response_due_at' => now()->addHours($targetHours),
            'routing_notes' => $routingNotes,
            'metadata' => [
                ...(is_array($inquiry->metadata) ? $inquiry->metadata : []),
                'acknowledgment_variant' => (string) data_get($route, 'acknowledgment_variant', 'standard'),
            ],
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::Submitted,
            body: 'Public inquiry submitted through the website.',
            metadata: ['pii_values_redacted' => true],
        );

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::Routed,
            body: (string) data_get($route, 'routing_note', 'Inquiry routed by category.'),
            metadata: $routingNotes['category_route'],
        );

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function assign(Inquiry $inquiry, User $assignee, ?User $actor = null, ?string $note = null): Inquiry
    {
        $previousAssignee = $inquiry->assigned_officer_id;
        $oldStatus = $inquiry->status;

        $inquiry = DB::transaction(function () use ($inquiry, $assignee, $actor, $note, $oldStatus): Inquiry {
            $inquiry->forceFill([
                'assigned_officer_id' => $assignee->id,
                'assigned_at' => now(),
                'status' => $inquiry->status === InquiryStatus::Pending
                    ? InquiryStatus::UnderReview
                    : $inquiry->status,
                'last_internal_activity_at' => now(),
            ])->save();

            $this->recordActivity(
                inquiry: $inquiry,
                type: InquiryActivityType::Assigned,
                actor: $actor,
                body: $note ?: 'Inquiry assigned to '.$assignee->name.'.',
                oldStatus: $oldStatus,
                newStatus: $inquiry->status,
                metadata: [
                    'assigned_to_user_id' => $assignee->id,
                    'pii_values_redacted' => true,
                ],
            );

            $this->auditCaseEvent('inquiry.assigned', $inquiry, $actor, [
                'assigned_officer_id' => $assignee->id,
                'previous_assigned_officer_id' => $inquiry->getOriginal('assigned_officer_id'),
            ]);

            return $inquiry;
        });

        if ($previousAssignee !== $assignee->id) {
            InquiryAssigned::dispatch($inquiry->fresh() ?? $inquiry, $assignee, $actor);
        }

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function markUnderReview(Inquiry $inquiry, ?User $actor = null, ?string $note = null): Inquiry
    {
        $oldStatus = $inquiry->status;

        $inquiry->forceFill([
            'status' => InquiryStatus::UnderReview,
            'assigned_officer_id' => $inquiry->assigned_officer_id ?? $actor?->id,
            'assigned_at' => $inquiry->assigned_at ?? ($actor instanceof User ? now() : null),
            'last_internal_activity_at' => now(),
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::StatusChanged,
            actor: $actor,
            body: $note ?: 'Inquiry marked under review.',
            oldStatus: $oldStatus,
            newStatus: InquiryStatus::UnderReview,
        );

        $this->auditCaseEvent('inquiry.under_review', $inquiry, $actor);

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function addInternalNote(Inquiry $inquiry, User $actor, string $note, ?string $templateKey = null): Inquiry
    {
        $inquiry->forceFill([
            'last_internal_activity_at' => now(),
            'internal_notes' => $this->appendText($inquiry->internal_notes, $note),
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::InternalNote,
            actor: $actor,
            body: $note,
            metadata: [
                'template_key' => $templateKey,
                'internal_note' => true,
            ],
        );

        $this->auditCaseEvent('inquiry.internal_note_added', $inquiry, $actor, [
            'template_key' => $templateKey,
            'pii_values_redacted' => true,
            'note_body_omitted' => true,
        ]);

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function markResponded(Inquiry $inquiry, User $actor, ?string $note = null): Inquiry
    {
        $inquiry->forceFill([
            'responded_at' => $inquiry->responded_at ?? now(),
            'first_responded_at' => $inquiry->first_responded_at ?? now(),
            'last_internal_activity_at' => now(),
            'status' => $inquiry->status === InquiryStatus::Pending ? InquiryStatus::UnderReview : $inquiry->status,
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::StaffResponse,
            actor: $actor,
            body: $note ?: 'First response milestone recorded.',
            oldStatus: null,
            newStatus: $inquiry->status,
            metadata: ['first_response_recorded' => true],
        );

        $this->auditCaseEvent('inquiry.responded', $inquiry, $actor);

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function resolve(Inquiry $inquiry, User $actor, ?string $note = null): Inquiry
    {
        $oldStatus = $inquiry->status;

        $inquiry->forceFill([
            'status' => InquiryStatus::Resolved,
            'responded_at' => $inquiry->responded_at ?? now(),
            'first_responded_at' => $inquiry->first_responded_at ?? now(),
            'resolved_at' => now(),
            'last_internal_activity_at' => now(),
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::Resolved,
            actor: $actor,
            body: $note ?: 'Inquiry resolved.',
            oldStatus: $oldStatus,
            newStatus: InquiryStatus::Resolved,
        );

        $this->auditCaseEvent('inquiry.resolved', $inquiry, $actor);

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function close(Inquiry $inquiry, User $actor, string $reason, ?string $note = null): Inquiry
    {
        $oldStatus = $inquiry->status;

        $inquiry->forceFill([
            'status' => InquiryStatus::Closed,
            'closed_at' => now(),
            'closed_reason' => $reason,
            'last_internal_activity_at' => now(),
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::Closed,
            actor: $actor,
            body: $note ?: 'Inquiry closed: '.$this->closureReasonLabel($reason).'.',
            oldStatus: $oldStatus,
            newStatus: InquiryStatus::Closed,
            metadata: ['closed_reason' => $reason],
        );

        $this->auditCaseEvent('inquiry.closed', $inquiry, $actor, [
            'closed_reason' => $reason,
        ]);

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function reopen(Inquiry $inquiry, User $actor, ?string $note = null): Inquiry
    {
        $oldStatus = $inquiry->status;

        $inquiry->forceFill([
            'status' => InquiryStatus::UnderReview,
            'resolved_at' => null,
            'closed_at' => null,
            'closed_reason' => null,
            'last_internal_activity_at' => now(),
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::Reopened,
            actor: $actor,
            body: $note ?: 'Inquiry reopened for follow-up review.',
            oldStatus: $oldStatus,
            newStatus: InquiryStatus::UnderReview,
        );

        $this->auditCaseEvent('inquiry.reopened', $inquiry, $actor);

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function recordOverdueReminder(Inquiry $inquiry, ?User $actor = null): Inquiry
    {
        $inquiry->forceFill([
            'overdue_reminder_sent_at' => now(),
            'last_internal_activity_at' => now(),
        ])->save();

        $this->recordActivity(
            inquiry: $inquiry,
            type: InquiryActivityType::OverdueReminder,
            actor: $actor,
            body: 'Overdue first-response reminder sent to staff.',
            metadata: ['pii_values_redacted' => true],
        );

        $this->auditCaseEvent('inquiry.overdue_reminder_sent', $inquiry, $actor);

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function recordActivity(
        Inquiry $inquiry,
        InquiryActivityType $type,
        ?User $actor = null,
        ?string $body = null,
        ?InquiryStatus $oldStatus = null,
        ?InquiryStatus $newStatus = null,
        array $metadata = [],
    ): InquiryActivity {
        return InquiryActivity::query()->create([
            'inquiry_id' => $inquiry->id,
            'actor_id' => $actor?->id,
            'type' => $type,
            'visibility' => 'internal',
            'body' => $body,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus?->value,
            'metadata' => $metadata,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function routeFor(InquiryCategory $category): array
    {
        $routes = config('belzamora.inquiries.category_routing', []);
        $route = is_array($routes) ? data_get($routes, $category->value, []) : [];

        return [
            'office_unit' => data_get($route, 'office_unit', config('belzamora.inquiries.case_management.default_office_unit')),
            'priority' => data_get($route, 'priority', InquiryPriority::Normal->value),
            'target_first_response_hours' => data_get($route, 'target_first_response_hours', config('belzamora.inquiries.unanswered_sla_hours', 48)),
            'acknowledgment_variant' => data_get($route, 'acknowledgment_variant', 'standard'),
            'routing_note' => data_get($route, 'routing_note', 'Route for general District Office review.'),
        ];
    }

    private function auditCaseEvent(string $event, Inquiry $inquiry, ?User $actor, array $newValues = []): void
    {
        $this->auditLog->record(
            event: $event,
            auditable: $inquiry,
            actor: $actor,
            newValues: [
                'reference_code' => $inquiry->reference_code,
                'status' => $inquiry->status->value,
                'priority' => $inquiry->priority->value,
                'office_unit' => $inquiry->office_unit,
                ...$newValues,
            ],
            metadata: [
                'pii_values_redacted' => true,
            ],
        );
    }

    private function appendText(?string $existing, string $addition): string
    {
        $addition = trim($addition);
        $existing = trim((string) $existing);

        return $existing === '' ? $addition : $existing.PHP_EOL.PHP_EOL.$addition;
    }

    private function closureReasonLabel(string $reason): string
    {
        $reasons = config('belzamora.inquiries.case_management.closure_reasons', []);

        return is_array($reasons) ? (string) ($reasons[$reason] ?? $reason) : $reason;
    }
}

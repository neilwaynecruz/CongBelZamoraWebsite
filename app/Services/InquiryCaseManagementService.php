<?php

namespace App\Services;

use App\Enums\AdminRole;
use App\Enums\InquiryActivityType;
use App\Enums\InquiryCategory;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Events\InquiryAssigned;
use App\Models\Inquiry;
use App\Models\InquiryActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $previousAssignee = null;

        $inquiry = DB::transaction(function () use ($inquiry, $assignee, $actor, $note, &$previousAssignee): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);
            $previousAssignee = $lockedInquiry->assigned_officer_id;
            $oldStatus = $lockedInquiry->status;

            $this->ensure(
                in_array($lockedInquiry->status, [InquiryStatus::Pending, InquiryStatus::UnderReview], true),
                'Only pending or under-review inquiries can be assigned.',
            );
            $this->ensure(
                $assignee->is_active && $assignee->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::InquiryOfficer),
                'Select an active Super Administrator or Inquiry Officer.',
                'assigned_officer_id',
            );

            $lockedInquiry->forceFill([
                'assigned_officer_id' => $assignee->id,
                'assigned_at' => now(),
                'status' => $lockedInquiry->status === InquiryStatus::Pending
                    ? InquiryStatus::UnderReview
                    : $lockedInquiry->status,
                'last_internal_activity_at' => now(),
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::Assigned,
                actor: $actor,
                body: $note ?: 'Inquiry assigned to '.$assignee->name.'.',
                oldStatus: $oldStatus,
                newStatus: $lockedInquiry->status,
                metadata: [
                    'assigned_to_user_id' => $assignee->id,
                    'pii_values_redacted' => true,
                ],
            );

            $this->auditCaseEvent('inquiry.assigned', $lockedInquiry, $actor, [
                'assigned_officer_id' => $assignee->id,
            ], [
                'assigned_officer_id' => $previousAssignee,
                'status' => $oldStatus->value,
            ]);

            return $lockedInquiry;
        });

        if ($previousAssignee !== $assignee->id) {
            InquiryAssigned::dispatch($inquiry->fresh() ?? $inquiry, $assignee, $actor);
        }

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function markUnderReview(Inquiry $inquiry, ?User $actor = null, ?string $note = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($inquiry, $actor, $note): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);
            $oldStatus = $lockedInquiry->status;
            $oldAssignee = $lockedInquiry->assigned_officer_id;

            $this->ensure($lockedInquiry->status === InquiryStatus::Pending, 'Only pending inquiries can be started.');
            $this->ensure(
                $lockedInquiry->assigned_officer_id !== null || $actor instanceof User,
                'Assign this inquiry before starting review.',
            );

            $lockedInquiry->forceFill([
                'status' => InquiryStatus::UnderReview,
                'assigned_officer_id' => $lockedInquiry->assigned_officer_id ?? $actor?->id,
                'assigned_at' => $lockedInquiry->assigned_at ?? ($actor instanceof User ? now() : null),
                'last_internal_activity_at' => now(),
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::StatusChanged,
                actor: $actor,
                body: $note ?: 'Inquiry marked under review.',
                oldStatus: $oldStatus,
                newStatus: InquiryStatus::UnderReview,
                metadata: [
                    'assigned_to_user_id' => $lockedInquiry->assigned_officer_id,
                    'pii_values_redacted' => true,
                ],
            );

            $this->auditCaseEvent('inquiry.under_review', $lockedInquiry, $actor, [
                'assigned_officer_id' => $lockedInquiry->assigned_officer_id,
            ], [
                'status' => $oldStatus->value,
                'assigned_officer_id' => $oldAssignee,
            ]);

            return $lockedInquiry;
        });

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function addInternalNote(Inquiry $inquiry, User $actor, string $note, ?string $templateKey = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($inquiry, $actor, $note, $templateKey): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);

            $this->ensure($lockedInquiry->status !== InquiryStatus::Anonymized, 'Anonymized inquiries cannot be changed.');

            $lockedInquiry->forceFill([
                'last_internal_activity_at' => now(),
                'internal_notes' => $this->appendText($lockedInquiry->internal_notes, $note),
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::InternalNote,
                actor: $actor,
                body: $note,
                metadata: [
                    'template_key' => $templateKey,
                    'internal_note' => true,
                ],
            );

            $this->auditCaseEvent('inquiry.internal_note_added', $lockedInquiry, $actor, [
                'template_key' => $templateKey,
                'pii_values_redacted' => true,
                'note_body_omitted' => true,
            ]);

            return $lockedInquiry;
        });

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function markResponded(Inquiry $inquiry, User $actor, ?string $note = null, ?string $templateKey = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($inquiry, $actor, $note, $templateKey): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);
            $oldStatus = $lockedInquiry->status;

            $this->ensure(
                in_array($lockedInquiry->status, [InquiryStatus::Pending, InquiryStatus::UnderReview], true),
                'Only open inquiries can record a response.',
            );
            $this->ensure($lockedInquiry->assigned_officer_id !== null, 'Assign this inquiry before recording a response.');
            $this->ensure($lockedInquiry->responded_at === null, 'A response milestone has already been recorded.');

            $lockedInquiry->forceFill([
                'responded_at' => now(),
                'first_responded_at' => $lockedInquiry->first_responded_at ?? now(),
                'last_internal_activity_at' => now(),
                'status' => $lockedInquiry->status === InquiryStatus::Pending ? InquiryStatus::UnderReview : $lockedInquiry->status,
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::StaffResponse,
                actor: $actor,
                body: $note ?: 'First response milestone recorded.',
                oldStatus: $oldStatus,
                newStatus: $lockedInquiry->status,
                metadata: [
                    'first_response_recorded' => true,
                    'template_key' => $templateKey,
                ],
            );

            $this->auditCaseEvent('inquiry.responded', $lockedInquiry, $actor, [
                'template_key' => $templateKey,
                'response_body_omitted' => true,
            ], [
                'status' => $oldStatus->value,
                'responded_at' => null,
            ]);

            return $lockedInquiry;
        });

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function resolve(Inquiry $inquiry, User $actor, ?string $note = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($inquiry, $actor, $note): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);
            $oldStatus = $lockedInquiry->status;

            $this->ensure(
                in_array($lockedInquiry->status, [InquiryStatus::Pending, InquiryStatus::UnderReview], true),
                'Only open inquiries can be resolved.',
            );
            $this->ensure($lockedInquiry->assigned_officer_id !== null, 'Assign this inquiry before resolving it.');

            $lockedInquiry->forceFill([
                'status' => InquiryStatus::Resolved,
                'responded_at' => $lockedInquiry->responded_at ?? now(),
                'first_responded_at' => $lockedInquiry->first_responded_at ?? now(),
                'resolved_at' => now(),
                'last_internal_activity_at' => now(),
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::Resolved,
                actor: $actor,
                body: $note ?: 'Inquiry resolved.',
                oldStatus: $oldStatus,
                newStatus: InquiryStatus::Resolved,
            );

            $this->auditCaseEvent('inquiry.resolved', $lockedInquiry, $actor, [
                'resolution_body_omitted' => true,
            ], [
                'status' => $oldStatus->value,
                'resolved_at' => null,
            ]);

            return $lockedInquiry;
        });

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function close(Inquiry $inquiry, User $actor, string $reason, ?string $note = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($inquiry, $actor, $reason, $note): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);
            $oldStatus = $lockedInquiry->status;
            $oldClosedReason = $lockedInquiry->closed_reason;
            $closureReasons = config('belzamora.inquiries.case_management.closure_reasons', []);

            $this->ensure($lockedInquiry->status === InquiryStatus::Resolved, 'Only resolved inquiries can be closed.');
            $this->ensure(
                is_array($closureReasons) && array_key_exists($reason, $closureReasons),
                'Select an approved closure reason.',
                'closed_reason',
            );

            $lockedInquiry->forceFill([
                'status' => InquiryStatus::Closed,
                'closed_at' => now(),
                'closed_reason' => $reason,
                'last_internal_activity_at' => now(),
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::Closed,
                actor: $actor,
                body: $note ?: 'Inquiry closed: '.$this->closureReasonLabel($reason).'.',
                oldStatus: $oldStatus,
                newStatus: InquiryStatus::Closed,
                metadata: ['closed_reason' => $reason],
            );

            $this->auditCaseEvent('inquiry.closed', $lockedInquiry, $actor, [
                'closed_reason' => $reason,
                'closure_body_omitted' => true,
            ], [
                'status' => $oldStatus->value,
                'closed_reason' => $oldClosedReason,
            ]);

            return $lockedInquiry;
        });

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function reopen(Inquiry $inquiry, User $actor, ?string $note = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($inquiry, $actor, $note): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);
            $oldStatus = $lockedInquiry->status;
            $oldClosedReason = $lockedInquiry->closed_reason;
            $oldAssignee = $lockedInquiry->assigned_officer_id;

            $this->ensure(
                in_array($lockedInquiry->status, [InquiryStatus::Resolved, InquiryStatus::Closed], true),
                'Only resolved or closed inquiries can be reopened.',
            );

            $lockedInquiry->forceFill([
                'status' => InquiryStatus::UnderReview,
                'assigned_officer_id' => $lockedInquiry->assigned_officer_id ?? $actor->id,
                'assigned_at' => $lockedInquiry->assigned_at ?? now(),
                'resolved_at' => null,
                'closed_at' => null,
                'closed_reason' => null,
                'last_internal_activity_at' => now(),
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::Reopened,
                actor: $actor,
                body: $note ?: 'Inquiry reopened for follow-up review.',
                oldStatus: $oldStatus,
                newStatus: InquiryStatus::UnderReview,
            );

            $this->auditCaseEvent('inquiry.reopened', $lockedInquiry, $actor, [
                'reopen_body_omitted' => true,
                'assigned_officer_id' => $lockedInquiry->assigned_officer_id,
            ], [
                'status' => $oldStatus->value,
                'closed_reason' => $oldClosedReason,
                'assigned_officer_id' => $oldAssignee,
            ]);

            return $lockedInquiry;
        });

        return $inquiry->fresh(['activities', 'assignedOfficer']) ?? $inquiry;
    }

    public function recordOverdueReminder(Inquiry $inquiry, ?User $actor = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($inquiry, $actor): Inquiry {
            $lockedInquiry = $this->lockInquiry($inquiry);

            $lockedInquiry->forceFill([
                'overdue_reminder_sent_at' => now(),
                'last_internal_activity_at' => now(),
            ])->save();

            $this->recordActivity(
                inquiry: $lockedInquiry,
                type: InquiryActivityType::OverdueReminder,
                actor: $actor,
                body: 'Overdue first-response reminder sent to staff.',
                metadata: ['pii_values_redacted' => true],
            );

            $this->auditCaseEvent('inquiry.overdue_reminder_sent', $lockedInquiry, $actor);

            return $lockedInquiry;
        });

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

    private function auditCaseEvent(
        string $event,
        Inquiry $inquiry,
        ?User $actor,
        array $newValues = [],
        array $oldValues = [],
    ): void {
        $this->auditLog->record(
            event: $event,
            auditable: $inquiry,
            actor: $actor,
            oldValues: $oldValues === [] ? null : $oldValues,
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

    private function lockInquiry(Inquiry $inquiry): Inquiry
    {
        return Inquiry::query()
            ->lockForUpdate()
            ->findOrFail($inquiry->getKey());
    }

    private function ensure(bool $condition, string $message, string $field = 'action'): void
    {
        if (! $condition) {
            throw ValidationException::withMessages([$field => $message]);
        }
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

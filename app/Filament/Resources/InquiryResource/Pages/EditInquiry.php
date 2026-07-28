<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Enums\InquiryActivityType;
use App\Enums\InquiryStatus;
use App\Events\InquiryAssigned;
use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\InquiryCaseManagementService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    private ?int $previousAssignedOfficerId = null;

    private ?InquiryStatus $previousStatus = null;

    protected function beforeSave(): void
    {
        /** @var Inquiry $record */
        $record = $this->record;
        $this->previousAssignedOfficerId = $record->assigned_officer_id;
        $this->previousStatus = $record->status;
    }

    protected function afterSave(): void
    {
        /** @var Inquiry $record */
        $record = $this->record;
        $actor = Auth::user();
        $actorUser = $actor instanceof User ? $actor : null;

        app(AuditLogService::class)->record(
            event: 'inquiry.updated',
            auditable: $record,
            actor: $actorUser,
            request: request(),
            newValues: [
                'reference_code' => $record->reference_code,
                'status' => $record->status->value,
                'assigned_officer_id' => $record->assigned_officer_id,
                'responded_at' => (string) $record->responded_at,
                'resolved_at' => (string) $record->resolved_at,
                'has_internal_notes' => filled($record->internal_notes),
            ],
            metadata: [
                'pii_values_redacted' => true,
                'internal_notes_omitted' => true,
            ],
        );

        if (
            $record->assigned_officer_id !== null
            && $record->assigned_officer_id !== $this->previousAssignedOfficerId
        ) {
            $assignee = User::query()->find($record->assigned_officer_id);

            if ($assignee instanceof User) {
                app(InquiryCaseManagementService::class)->recordActivity(
                    inquiry: $record,
                    type: InquiryActivityType::Assigned,
                    actor: $actorUser,
                    body: 'Inquiry assignment updated from the edit form.',
                    oldStatus: $this->previousStatus,
                    newStatus: $record->status,
                    metadata: [
                        'assigned_to_user_id' => $assignee->id,
                        'source' => 'filament_edit_form',
                        'pii_values_redacted' => true,
                    ],
                );

                InquiryAssigned::dispatch(
                    $record,
                    $assignee,
                    $actorUser,
                );
            }
        }

        if ($this->previousStatus !== null && $record->status !== $this->previousStatus) {
            app(InquiryCaseManagementService::class)->recordActivity(
                inquiry: $record,
                type: InquiryActivityType::StatusChanged,
                actor: $actorUser,
                body: 'Inquiry status updated from the edit form.',
                oldStatus: $this->previousStatus,
                newStatus: $record->status,
                metadata: [
                    'source' => 'filament_edit_form',
                    'pii_values_redacted' => true,
                ],
            );
        }
    }
}

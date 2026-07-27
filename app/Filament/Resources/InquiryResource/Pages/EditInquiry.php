<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Events\InquiryAssigned;
use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\AuditLogService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    private ?int $previousAssignedOfficerId = null;

    protected function beforeSave(): void
    {
        /** @var Inquiry $record */
        $record = $this->record;
        $this->previousAssignedOfficerId = $record->assigned_officer_id;
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
                InquiryAssigned::dispatch(
                    $record,
                    $assignee,
                    $actorUser,
                );
            }
        }
    }
}

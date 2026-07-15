<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Services\AuditLogService;
use Filament\Resources\Pages\EditRecord;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    protected function afterSave(): void
    {
        /** @var Inquiry $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'inquiry.updated',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'reference_code' => $record->reference_code,
                'status' => $record->status->value,
                'assigned_officer_id' => $record->assigned_officer_id,
                'responded_at' => (string) $record->responded_at,
                'resolved_at' => (string) $record->resolved_at,
            ],
            metadata: [
                'pii_values_redacted' => true,
            ],
        );
    }
}

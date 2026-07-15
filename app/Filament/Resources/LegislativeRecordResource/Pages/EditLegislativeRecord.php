<?php

namespace App\Filament\Resources\LegislativeRecordResource\Pages;

use App\Filament\Resources\LegislativeRecordResource;
use App\Models\LegislativeRecord;
use App\Services\AuditLogService;
use Filament\Resources\Pages\EditRecord;

class EditLegislativeRecord extends EditRecord
{
    protected static string $resource = LegislativeRecordResource::class;

    protected function afterSave(): void
    {
        /** @var LegislativeRecord $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'legislative_record.updated',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'bill_number' => $record->bill_number,
                'public_title' => $record->public_title,
                'published_at' => (string) $record->published_at,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );
    }
}

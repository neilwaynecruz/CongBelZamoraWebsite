<?php

namespace App\Filament\Resources\LegislativeRecordResource\Pages;

use App\Filament\Resources\LegislativeRecordResource;
use App\Models\LegislativeRecord;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateLegislativeRecord extends CreateRecord
{
    protected static string $resource = LegislativeRecordResource::class;

    protected function afterCreate(): void
    {
        /** @var LegislativeRecord $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'legislative_record.created',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'bill_number' => $record->bill_number,
                'public_title' => $record->public_title,
                'published_at' => (string) $record->published_at,
            ],
        );
    }
}

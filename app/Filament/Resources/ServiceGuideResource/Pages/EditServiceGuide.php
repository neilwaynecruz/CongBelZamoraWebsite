<?php

namespace App\Filament\Resources\ServiceGuideResource\Pages;

use App\Filament\Resources\ServiceGuideResource;
use App\Models\ServiceGuide;
use App\Services\AuditLogService;
use Filament\Resources\Pages\EditRecord;

class EditServiceGuide extends EditRecord
{
    protected static string $resource = ServiceGuideResource::class;

    protected function afterSave(): void
    {
        /** @var ServiceGuide $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'service_guide.updated',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'label' => $record->label,
                'slug' => $record->slug,
                'responsible_office_type' => $record->responsible_office_type->value,
                'published_at' => (string) $record->published_at,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );
    }
}

<?php

namespace App\Filament\Resources\ServiceGuideResource\Pages;

use App\Filament\Resources\ServiceGuideResource;
use App\Models\ServiceGuide;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceGuide extends CreateRecord
{
    protected static string $resource = ServiceGuideResource::class;

    protected function afterCreate(): void
    {
        /** @var ServiceGuide $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'service_guide.created',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'label' => $record->label,
                'slug' => $record->slug,
                'responsible_office_type' => $record->responsible_office_type->value,
                'published_at' => (string) $record->published_at,
            ],
        );
    }
}

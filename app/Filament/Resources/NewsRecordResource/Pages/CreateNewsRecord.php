<?php

namespace App\Filament\Resources\NewsRecordResource\Pages;

use App\Filament\Resources\NewsRecordResource;
use App\Models\NewsRecord;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsRecord extends CreateRecord
{
    protected static string $resource = NewsRecordResource::class;

    protected function afterCreate(): void
    {
        /** @var NewsRecord $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'news_record.created',
            auditable: $record,
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'status' => $record->status->value->value,
                'published_at' => (string) $record->published_at,
            ],
        );
    }
}

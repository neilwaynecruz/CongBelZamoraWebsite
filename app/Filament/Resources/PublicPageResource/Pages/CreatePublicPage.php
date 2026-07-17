<?php

namespace App\Filament\Resources\PublicPageResource\Pages;

use App\Filament\Resources\PublicPageResource;
use App\Models\PublicPage;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicPage extends CreateRecord
{
    protected static string $resource = PublicPageResource::class;

    protected function afterCreate(): void
    {
        /** @var PublicPage $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'public_page.created',
            auditable: $record,
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'component' => $record->component,
                'published_at' => (string) $record->published_at,
            ],
        );
    }
}

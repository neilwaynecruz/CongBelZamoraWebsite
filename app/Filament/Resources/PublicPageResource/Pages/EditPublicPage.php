<?php

namespace App\Filament\Resources\PublicPageResource\Pages;

use App\Filament\Resources\PublicPageResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\PublicPage;
use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPublicPage extends EditRecord
{
    use SoftDeleteHeaderActions;

    protected static string $resource = PublicPageResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected function afterSave(): void
    {
        /** @var PublicPage $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'public_page.updated',
            auditable: $record,
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'component' => $record->component,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );

        app(PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

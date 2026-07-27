<?php

namespace App\Filament\Resources\NewsRecordResource\Pages;

use App\Filament\Resources\NewsRecordResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\NewsRecord;
use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditNewsRecord extends EditRecord
{
    use SoftDeleteHeaderActions;

    protected static string $resource = NewsRecordResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected function afterSave(): void
    {
        /** @var NewsRecord $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'news_record.updated',
            auditable: $record,
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'status' => $record->status->value->value,
                'published_at' => (string) $record->published_at,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );

        app(PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

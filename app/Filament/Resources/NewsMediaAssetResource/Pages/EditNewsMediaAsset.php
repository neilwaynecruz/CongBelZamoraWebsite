<?php

namespace App\Filament\Resources\NewsMediaAssetResource\Pages;

use App\Filament\Resources\NewsMediaAssetResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\NewsMediaAsset;
use App\Services\AuditLogService;
use Filament\Resources\Pages\EditRecord;

class EditNewsMediaAsset extends EditRecord
{
    use SoftDeleteHeaderActions;

    /** @return array<int, \\Filament\\Actions\\Action> */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected static string $resource = NewsMediaAssetResource::class;

    protected function afterSave(): void
    {
        /** @var NewsMediaAsset $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'news_media_asset.updated',
            auditable: $record,
            request: request(),
            newValues: [
                'news_record_id' => $record->news_record_id,
                'title' => $record->title,
                'verified_at' => (string) $record->verified_at,
            ],
        );

        app(\App\Services\PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

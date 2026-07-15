<?php

namespace App\Filament\Resources\NewsMediaAssetResource\Pages;

use App\Filament\Resources\NewsMediaAssetResource;
use App\Models\NewsMediaAsset;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsMediaAsset extends CreateRecord
{
    protected static string $resource = NewsMediaAssetResource::class;

    protected function afterCreate(): void
    {
        /** @var NewsMediaAsset $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'news_media_asset.created',
            auditable: $record,
            request: request(),
            newValues: [
                'news_record_id' => $record->news_record_id,
                'title' => $record->title,
                'source_url' => $record->source_url,
            ],
        );
    }
}

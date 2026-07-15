<?php

namespace App\Filament\Resources\NewsMediaAssetResource\Pages;

use App\Filament\Resources\NewsMediaAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsMediaAssets extends ListRecords
{
    protected static string $resource = NewsMediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

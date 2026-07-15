<?php

namespace App\Filament\Resources\ServiceGuideResource\Pages;

use App\Filament\Resources\ServiceGuideResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceGuides extends ListRecords
{
    protected static string $resource = ServiceGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

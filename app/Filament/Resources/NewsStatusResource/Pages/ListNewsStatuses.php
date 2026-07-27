<?php

namespace App\Filament\Resources\NewsStatusResource\Pages;

use App\Filament\Resources\NewsStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsStatuses extends ListRecords
{
    protected static string $resource = NewsStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

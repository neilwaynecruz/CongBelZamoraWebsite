<?php

namespace App\Filament\Resources\LegislativeStatusResource\Pages;

use App\Filament\Resources\LegislativeStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegislativeStatuses extends ListRecords
{
    protected static string $resource = LegislativeStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

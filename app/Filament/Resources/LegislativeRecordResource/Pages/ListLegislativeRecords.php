<?php

namespace App\Filament\Resources\LegislativeRecordResource\Pages;

use App\Filament\Resources\LegislativeRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegislativeRecords extends ListRecords
{
    protected static string $resource = LegislativeRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

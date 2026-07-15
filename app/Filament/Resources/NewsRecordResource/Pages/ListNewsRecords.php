<?php

namespace App\Filament\Resources\NewsRecordResource\Pages;

use App\Filament\Resources\NewsRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsRecords extends ListRecords
{
    protected static string $resource = NewsRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

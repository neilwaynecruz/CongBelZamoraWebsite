<?php

namespace App\Filament\Resources\ProjectPartnerResource\Pages;

use App\Filament\Resources\ProjectPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectPartners extends ListRecords
{
    protected static string $resource = ProjectPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

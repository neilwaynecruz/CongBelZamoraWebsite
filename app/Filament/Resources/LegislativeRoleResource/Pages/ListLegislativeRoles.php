<?php

namespace App\Filament\Resources\LegislativeRoleResource\Pages;

use App\Filament\Resources\LegislativeRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegislativeRoles extends ListRecords
{
    protected static string $resource = LegislativeRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

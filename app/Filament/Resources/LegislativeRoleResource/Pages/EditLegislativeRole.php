<?php

namespace App\Filament\Resources\LegislativeRoleResource\Pages;

use App\Filament\Resources\LegislativeRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLegislativeRole extends EditRecord
{
    protected static string $resource = LegislativeRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

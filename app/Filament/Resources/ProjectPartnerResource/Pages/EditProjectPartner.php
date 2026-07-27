<?php

namespace App\Filament\Resources\ProjectPartnerResource\Pages;

use App\Filament\Resources\ProjectPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectPartner extends EditRecord
{
    protected static string $resource = ProjectPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

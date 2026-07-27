<?php

namespace App\Filament\Resources\NewsStatusResource\Pages;

use App\Filament\Resources\NewsStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewsStatus extends EditRecord
{
    protected static string $resource = NewsStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

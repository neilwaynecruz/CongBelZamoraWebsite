<?php

namespace App\Filament\Resources\ContentApprovalResource\Pages;

use App\Filament\Resources\ContentApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContentApproval extends ViewRecord
{
    protected static string $resource = ContentApprovalResource::class;

    /**
     * @return array<int, Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

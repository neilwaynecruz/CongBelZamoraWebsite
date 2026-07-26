<?php

namespace App\Filament\Resources\ContentApprovalResource\Pages;

use App\Filament\Resources\ContentApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentApprovals extends ListRecords
{
    protected static string $resource = ContentApprovalResource::class;

    /**
     * @return array<int, Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}

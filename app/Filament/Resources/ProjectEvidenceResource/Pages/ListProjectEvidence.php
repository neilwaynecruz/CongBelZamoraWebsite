<?php

namespace App\Filament\Resources\ProjectEvidenceResource\Pages;

use App\Filament\Resources\ProjectEvidenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectEvidence extends ListRecords
{
    protected static string $resource = ProjectEvidenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

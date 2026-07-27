<?php

namespace App\Filament\Resources\LegislativeTopicResource\Pages;

use App\Filament\Resources\LegislativeTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLegislativeTopics extends ListRecords
{
    protected static string $resource = LegislativeTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

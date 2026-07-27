<?php

namespace App\Filament\Resources\SourceRecordResource\Pages;

use App\Filament\Resources\SourceRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSourceRecords extends ManageRecords
{
    protected static string $resource = SourceRecordResource::class;

    public function getTitle(): string
    {
        return 'Source Records';
    }

    public function getSubheading(): ?string
    {
        return 'Review official sources, ownership, expiry, and freshness dates across all public content.';
    }

    /**
     * @return array<int, Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

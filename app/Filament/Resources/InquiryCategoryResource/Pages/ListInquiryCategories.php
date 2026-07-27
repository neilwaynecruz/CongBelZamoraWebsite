<?php

namespace App\Filament\Resources\InquiryCategoryResource\Pages;

use App\Filament\Resources\InquiryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInquiryCategories extends ListRecords
{
    protected static string $resource = InquiryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

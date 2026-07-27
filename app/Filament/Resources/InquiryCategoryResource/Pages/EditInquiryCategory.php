<?php

namespace App\Filament\Resources\InquiryCategoryResource\Pages;

use App\Filament\Resources\InquiryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInquiryCategory extends EditRecord
{
    protected static string $resource = InquiryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

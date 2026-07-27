<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryCategoryResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\InquiryCategory;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class InquiryCategoryResource extends Resource
{
    protected static ?string $model = InquiryCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return ReferenceDataResourceSupport::form($form, [
            'code' => true,
            'name' => false,
            'slug' => false,
            'label' => true,
        ]);
    }

    public static function table(Table $table): Table
    {
        return ReferenceDataResourceSupport::table($table, ['code', 'label', 'sort_order']);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiryCategories::route('/'),
            'create' => Pages\CreateInquiryCategory::route('/create'),
            'edit' => Pages\EditInquiryCategory::route('/{record}/edit'),
        ];
    }
}

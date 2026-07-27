<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsCategoryResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\NewsCategory;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return ReferenceDataResourceSupport::form($form);
    }

    public static function table(Table $table): Table
    {
        return ReferenceDataResourceSupport::table($table, ['name', 'slug', 'sort_order']);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsCategories::route('/'),
            'create' => Pages\CreateNewsCategory::route('/create'),
            'edit' => Pages\EditNewsCategory::route('/{record}/edit'),
        ];
    }
}

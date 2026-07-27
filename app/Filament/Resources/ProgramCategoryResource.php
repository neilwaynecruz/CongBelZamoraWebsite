<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramCategoryResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\ProgramCategory;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ProgramCategoryResource extends Resource
{
    protected static ?string $model = ProgramCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 10;

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
            'index' => Pages\ListProgramCategories::route('/'),
            'create' => Pages\CreateProgramCategory::route('/create'),
            'edit' => Pages\EditProgramCategory::route('/{record}/edit'),
        ];
    }
}

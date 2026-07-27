<?php

namespace App\Filament\Resources;

use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Filament\Resources\NewsStatusResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\NewsStatus;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class NewsStatusResource extends Resource
{
    protected static ?string $model = NewsStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 41;

    public static function form(Form $form): Form
    {
        return ReferenceDataResourceSupport::form($form, [
            'label' => true,
            'value_options' => ReferenceDataResourceSupport::enumOptions(NewsStatusValue::cases()),
            'tone_options' => ReferenceDataResourceSupport::enumOptions(NewsStatusTone::cases()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return ReferenceDataResourceSupport::table($table, ['label', 'value', 'tone', 'sort_order']);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsStatuses::route('/'),
            'create' => Pages\CreateNewsStatus::route('/create'),
            'edit' => Pages\EditNewsStatus::route('/{record}/edit'),
        ];
    }
}

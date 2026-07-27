<?php

namespace App\Filament\Resources;

use App\Enums\LegislativeStatusTone;
use App\Filament\Resources\LegislativeStatusResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\LegislativeStatus;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class LegislativeStatusResource extends Resource
{
    protected static ?string $model = LegislativeStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return ReferenceDataResourceSupport::form($form, [
            'label' => true,
            'tone_options' => ReferenceDataResourceSupport::enumOptions(LegislativeStatusTone::cases()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return ReferenceDataResourceSupport::table($table, ['label', 'tone', 'sort_order']);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegislativeStatuses::route('/'),
            'create' => Pages\CreateLegislativeStatus::route('/create'),
            'edit' => Pages\EditLegislativeStatus::route('/{record}/edit'),
        ];
    }
}

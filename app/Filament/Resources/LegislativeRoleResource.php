<?php

namespace App\Filament\Resources;

use App\Enums\LegislativeRoleValue;
use App\Filament\Resources\LegislativeRoleResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\LegislativeRole;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class LegislativeRoleResource extends Resource
{
    protected static ?string $model = LegislativeRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return ReferenceDataResourceSupport::form($form, [
            'label' => true,
            'value_options' => ReferenceDataResourceSupport::enumOptions(LegislativeRoleValue::cases()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return ReferenceDataResourceSupport::table($table, ['label', 'value', 'sort_order']);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegislativeRoles::route('/'),
            'create' => Pages\CreateLegislativeRole::route('/create'),
            'edit' => Pages\EditLegislativeRole::route('/{record}/edit'),
        ];
    }
}

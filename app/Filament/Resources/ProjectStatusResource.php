<?php

namespace App\Filament\Resources;

use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Filament\Resources\ProjectStatusResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\ProjectStatus;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ProjectStatusResource extends Resource
{
    protected static ?string $model = ProjectStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 31;

    public static function form(Form $form): Form
    {
        return ReferenceDataResourceSupport::form($form, [
            'label' => true,
            'value_options' => ReferenceDataResourceSupport::enumOptions(ProjectStatusValue::cases()),
            'tone_options' => ReferenceDataResourceSupport::enumOptions(ProjectStatusTone::cases()),
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
            'index' => Pages\ListProjectStatuses::route('/'),
            'create' => Pages\CreateProjectStatus::route('/create'),
            'edit' => Pages\EditProjectStatus::route('/{record}/edit'),
        ];
    }
}

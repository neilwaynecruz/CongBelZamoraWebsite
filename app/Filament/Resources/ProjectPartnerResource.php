<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectPartnerResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\ProjectPartner;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ProjectPartnerResource extends Resource
{
    protected static ?string $model = ProjectPartner::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 32;

    public static function form(Form $form): Form
    {
        return ReferenceDataResourceSupport::form($form, [
            'description' => false,
            'sort_order' => false,
            'office_type' => true,
            'website_url' => true,
            'notes' => true,
        ]);
    }

    public static function table(Table $table): Table
    {
        return ReferenceDataResourceSupport::table($table, ['name', 'slug', 'office_type', 'website_url']);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectPartners::route('/'),
            'create' => Pages\CreateProjectPartner::route('/create'),
            'edit' => Pages\EditProjectPartner::route('/{record}/edit'),
        ];
    }
}

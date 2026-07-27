<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegislativeTopicResource\Pages;
use App\Filament\Support\ReferenceDataResourceSupport;
use App\Models\LegislativeTopic;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class LegislativeTopicResource extends Resource
{
    protected static ?string $model = LegislativeTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 22;

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
            'index' => Pages\ListLegislativeTopics::route('/'),
            'create' => Pages\CreateLegislativeTopic::route('/create'),
            'edit' => Pages\EditLegislativeTopic::route('/{record}/edit'),
        ];
    }
}

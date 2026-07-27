<?php

namespace App\Filament\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LegislativeSourceHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'sourceHistories';

    protected static ?string $title = 'Source History';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('source_label')->required()->maxLength(255),
            Forms\Components\TextInput::make('source_url')->required()->url()->maxLength(255),
            Forms\Components\TextInput::make('status_snapshot')->required()->maxLength(255),
            Forms\Components\Select::make('checked_by_id')->relationship('checker', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('checked_at')->timezone('Asia/Manila'),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source_label')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('status_snapshot')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('checker.name')->label('Checked by')->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('checked_at')->dateTime(timezone: 'Asia/Manila')->sortable(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }
}

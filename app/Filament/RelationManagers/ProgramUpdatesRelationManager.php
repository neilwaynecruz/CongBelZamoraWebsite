<?php

namespace App\Filament\RelationManagers;

use App\Enums\ProgramStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramUpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';

    protected static ?string $title = 'Program Updates';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('summary')->required()->rows(3)->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options(ProgramStatus::options())
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('source_label')->maxLength(255),
            Forms\Components\TextInput::make('source_url')->url()->maxLength(255),
            Forms\Components\DateTimePicker::make('verified_at')->timezone('Asia/Manila'),
            Forms\Components\Select::make('content_owner_id')->relationship('contentOwner', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('published_at')->timezone('Asia/Manila'),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('contentOwner.name')->label('Owner')->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('published_at')->dateTime(timezone: 'Asia/Manila')->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }
}

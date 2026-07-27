<?php

namespace App\Filament\RelationManagers;

use App\Enums\SourceType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SourceRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'sourceRecords';

    protected static ?string $title = 'Source Records';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\TextInput::make('url')->url()->maxLength(255),
            Forms\Components\Select::make('source_type')
                ->options(collect(SourceType::cases())->mapWithKeys(fn (SourceType $type): array => [$type->value => $type->label()])->all())
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('source_tier')->default('tier_1')->required()->maxLength(40),
            Forms\Components\DateTimePicker::make('verified_at')->timezone('Asia/Manila'),
            Forms\Components\DateTimePicker::make('next_review_at')->timezone('Asia/Manila'),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\Textarea::make('internal_notes')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('source_type')->badge(),
                Tables\Columns\TextColumn::make('source_tier')->badge(),
                Tables\Columns\TextColumn::make('next_review_at')->dateTime(timezone: 'Asia/Manila')->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('needs_review')
                    ->label('Due for review')
                    ->query(fn ($query) => $query->needsReview()),
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

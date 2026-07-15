<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectEvidenceResource\Pages;
use App\Models\ProjectEvidence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectEvidenceResource extends Resource
{
    protected static ?string $model = ProjectEvidence::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Public Content';

    protected static ?int $navigationSort = 51;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Evidence record')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_url')
                            ->required()
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('media_path')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\Select::make('verified_by_id')
                            ->relationship('verifier', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('caption')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('rights_note')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectEvidence::route('/'),
            'create' => Pages\CreateProjectEvidence::route('/create'),
            'view' => Pages\ViewProjectEvidence::route('/{record}'),
            'edit' => Pages\EditProjectEvidence::route('/{record}/edit'),
        ];
    }
}

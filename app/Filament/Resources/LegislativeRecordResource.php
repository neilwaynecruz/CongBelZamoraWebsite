<?php

namespace App\Filament\Resources;

use App\Enums\LegislativeRoleValue;
use App\Enums\LegislativeStatusTone;
use App\Filament\Resources\LegislativeRecordResource\Pages;
use App\Models\LegislativeRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LegislativeRecordResource extends Resource
{
    protected static ?string $model = LegislativeRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Public Content';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Legislative record')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('bill_number')
                            ->label('Bill / record number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('official_title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('public_title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('congress')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('committee')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('legislative_role_id')
                            ->relationship('role', 'label')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('legislative_status_id')
                            ->relationship('status', 'label')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('legislative_topic_id')
                            ->relationship('topic', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('filed_at_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('filed_at')
                            ->timezone('Asia/Manila'),
                    ]),
                Forms\Components\Section::make('Public explanation')
                    ->schema([
                        Forms\Components\Textarea::make('plain_language_summary')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('why_it_matters')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('san_juan_relevance')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Official source and review')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('official_source_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('official_source_url')
                            ->required()
                            ->url()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('review_due_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\Select::make('content_owner_id')
                            ->relationship('contentOwner', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('approved_by_id')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Section::make('Source history')
                    ->schema([
                        Forms\Components\Repeater::make('sourceHistories')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('source_label')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('source_url')
                                    ->required()
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('status_snapshot')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('checked_by_id')
                                    ->relationship('checker', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\DateTimePicker::make('checked_at')
                                    ->timezone('Asia/Manila'),
                                Forms\Components\Textarea::make('notes')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill_number')
                    ->label('Record')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('public_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role.value')
                    ->label('Role')
                    ->formatStateUsing(fn (LegislativeRoleValue $state): string => $state->label())
                    ->badge(),
                Tables\Columns\TextColumn::make('status.label')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('status.tone')
                    ->formatStateUsing(fn (LegislativeStatusTone $state): string => $state->label())
                    ->badge(),
                Tables\Columns\TextColumn::make('topic.name')
                    ->label('Topic')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
                Tables\Columns\TextColumn::make('review_due_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('legislative_role_id')
                    ->label('Role')
                    ->relationship('role', 'label'),
                Tables\Filters\SelectFilter::make('legislative_status_id')
                    ->label('Status')
                    ->relationship('status', 'label'),
                Tables\Filters\SelectFilter::make('legislative_topic_id')
                    ->label('Topic')
                    ->relationship('topic', 'name'),
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
            'index' => Pages\ListLegislativeRecords::route('/'),
            'create' => Pages\CreateLegislativeRecord::route('/create'),
            'view' => Pages\ViewLegislativeRecord::route('/{record}'),
            'edit' => Pages\EditLegislativeRecord::route('/{record}/edit'),
        ];
    }
}

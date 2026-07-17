<?php

namespace App\Filament\Resources;

use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project record')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('project_category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('project_status_id')
                            ->relationship('status', 'label')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('barangay')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('responsible_office')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('primary_partner_id')
                            ->relationship('primaryPartner', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('partner_label')
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Public explanation')
                    ->schema([
                        Forms\Components\Textarea::make('summary')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('evidence_summary')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('impact_note')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('image_guidance')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Official source and review')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('source_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_url')
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
                Forms\Components\Section::make('Evidence')
                    ->schema([
                        Forms\Components\Repeater::make('evidences')
                            ->relationship()
                            ->schema([
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
                                Forms\Components\Textarea::make('caption')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('rights_note')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.value')
                    ->label('Status')
                    ->formatStateUsing(fn (ProjectStatusValue $state): string => $state->label())
                    ->badge()
                    ->color(fn (ProjectStatusValue $state): string => match ($state) {
                        ProjectStatusValue::Completed => 'success',
                        ProjectStatusValue::Ongoing => 'info',
                        ProjectStatusValue::SourcePending => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status.tone')
                    ->formatStateUsing(fn (ProjectStatusTone $state): string => $state->label())
                    ->badge()
                    ->color(fn (ProjectStatusTone $state): string => match ($state) {
                        ProjectStatusTone::Completed => 'success',
                        ProjectStatusTone::Ongoing => 'info',
                        ProjectStatusTone::SourcePending => 'warning',
                    }),
                Tables\Columns\TextColumn::make('barangay')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('partner_label')
                    ->label('Partner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
                Tables\Columns\TextColumn::make('review_due_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('project_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('project_status_id')
                    ->label('Status')
                    ->relationship('status', 'label'),
                Tables\Filters\SelectFilter::make('barangay')
                    ->options(fn (): array => Project::query()
                        ->select('barangay')
                        ->distinct()
                        ->orderBy('barangay')
                        ->pluck('barangay', 'barangay')
                        ->all()),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}

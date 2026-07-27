<?php

namespace App\Filament\Resources;

use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Filament\RelationManagers\ContentApprovalsRelationManager;
use App\Filament\RelationManagers\NewsMediaAssetsRelationManager;
use App\Filament\RelationManagers\SourceRecordsRelationManager;
use App\Filament\Resources\NewsRecordResource\Pages;
use App\Filament\Support\SoftDeletableResource;
use App\Models\NewsRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsRecordResource extends Resource
{
    use SoftDeletableResource;

    protected static ?string $model = NewsRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'News & Media';

    protected static ?int $navigationSort = 60;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'summary', 'official_update', 'slug'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('News record')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('news_category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('news_status_id')
                            ->relationship('status', 'label')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('published_at_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->timezone('Asia/Manila')
                            ->required(),
                        Forms\Components\TextInput::make('public_updated_at_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('public_updated_at')
                            ->timezone('Asia/Manila'),
                    ]),
                Forms\Components\Section::make('Public copy')
                    ->schema([
                        Forms\Components\Textarea::make('summary')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('official_update')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('next_step')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('stale_note')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Source and review')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('source_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_url')
                            ->required()
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('owner_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('source_verified_at')
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
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Section::make('Media assets')
                    ->schema([
                        Forms\Components\Repeater::make('mediaAssets')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('media_type')
                                    ->required()
                                    ->maxLength(40)
                                    ->default('image'),
                                Forms\Components\TextInput::make('media_path')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('source_label')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('source_url')
                                    ->required()
                                    ->url()
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
                                Forms\Components\Textarea::make('alt_text')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('rights_note')
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.value')
                    ->label('Status')
                    ->formatStateUsing(fn (NewsStatusValue $state): string => $state->label())
                    ->badge()
                    ->color(fn (NewsStatusValue $state): string => match ($state) {
                        NewsStatusValue::Current => 'success',
                        NewsStatusValue::Archived => 'gray',
                        NewsStatusValue::StaleReview => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status.tone')
                    ->formatStateUsing(fn (NewsStatusTone $state): string => $state->label())
                    ->badge()
                    ->color(fn (NewsStatusTone $state): string => match ($state) {
                        NewsStatusTone::Current => 'success',
                        NewsStatusTone::Archived => 'gray',
                        NewsStatusTone::StaleReview => 'warning',
                    }),
                Tables\Columns\TextColumn::make('published_at_label')
                    ->label('Published'),
                Tables\Columns\TextColumn::make('public_updated_at_label')
                    ->label('Updated'),
                Tables\Columns\TextColumn::make('owner_label')
                    ->label('Owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('review_due_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('news_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('news_status_id')
                    ->label('Status')
                    ->relationship('status', 'label'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @return array<int, class-string>
     */
    public static function getRelations(): array
    {
        return [
            NewsMediaAssetsRelationManager::class,
            SourceRecordsRelationManager::class,
            ContentApprovalsRelationManager::class,
        ];
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsRecords::route('/'),
            'create' => Pages\CreateNewsRecord::route('/create'),
            'view' => Pages\ViewNewsRecord::route('/{record}'),
            'edit' => Pages\EditNewsRecord::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsMediaAssetResource\Pages;
use App\Models\NewsMediaAsset;
use App\Support\ApprovedMediaForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsMediaAssetResource extends Resource
{
    protected static ?string $model = NewsMediaAsset::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'News & Media';

    protected static ?int $navigationSort = 61;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Media asset')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('news_record_id')
                            ->relationship('newsRecord', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('media_type')
                            ->required()
                            ->default('image')
                            ->maxLength(40),
                        Forms\Components\TextInput::make('source_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_url')
                            ->required()
                            ->url()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->timezone('Asia/Manila')
                            ->helperText('Set only after rights, caption, and alt text are confirmed.'),
                        Forms\Components\Select::make('verified_by_id')
                            ->relationship('verifier', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                ...ApprovedMediaForm::uploadFields(
                    directoryConfigKey: 'news_directory',
                    sectionDescription: 'Upload only rights-cleared official assets. Do not upload citizen ID scans, medical records, school documents, or other sensitive files.',
                ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('media_path')
                    ->label('Preview')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(null),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('newsRecord.title')
                    ->label('News record')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('media_type')
                    ->badge(),
                Tables\Columns\IconColumn::make('rights_ready')
                    ->label('Rights ready')
                    ->boolean()
                    ->getStateUsing(fn (NewsMediaAsset $record): bool => self::isRightsReady($record)),
                Tables\Columns\TextColumn::make('source_label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('news_record_id')
                    ->label('News record')
                    ->relationship('newsRecord', 'title'),
                Tables\Filters\Filter::make('unverified')
                    ->label('Needs verification')
                    ->query(fn (Builder $query): Builder => $query->whereNull('verified_at')),
                Tables\Filters\Filter::make('missing_file')
                    ->label('Missing uploaded file')
                    ->query(fn (Builder $query): Builder => $query->whereNull('media_path')->orWhere('media_path', '')),
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
            'index' => Pages\ListNewsMediaAssets::route('/'),
            'create' => Pages\CreateNewsMediaAsset::route('/create'),
            'view' => Pages\ViewNewsMediaAsset::route('/{record}'),
            'edit' => Pages\EditNewsMediaAsset::route('/{record}/edit'),
        ];
    }

    private static function isRightsReady(NewsMediaAsset $record): bool
    {
        return filled($record->media_path)
            && filled($record->caption)
            && filled($record->alt_text)
            && filled($record->rights_note)
            && $record->verified_at !== null;
    }
}

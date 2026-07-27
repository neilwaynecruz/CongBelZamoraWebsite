<?php

namespace App\Filament\Resources;

use App\Filament\Support\SoftDeletableResource;

use App\Filament\Resources\PublicPageResource\Pages;
use App\Filament\RelationManagers\ContentApprovalsRelationManager;
use App\Filament\RelationManagers\SourceRecordsRelationManager;
use App\Models\PublicPage;
use App\Support\PublicPageProps;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PublicPageResource extends Resource
{
    use SoftDeletableResource;

    protected static ?string $model = PublicPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Public Content';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Page')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('component')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->timezone('Asia/Manila')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Inertia props')
                    ->schema([
                        Forms\Components\Textarea::make('props')
                            ->required()
                            ->rows(18)
                            ->formatStateUsing(fn (?array $state): string => json_encode($state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}')
                            ->dehydrateStateUsing(fn (?string $state): array => PublicPageProps::decode($state))
                            ->rules([
                                'json',
                                fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    PublicPageProps::validateJsonForSlug(
                                        is_string($value) ? $value : null,
                                        (string) $get('slug'),
                                        (string) $get('component'),
                                        $fail,
                                    );
                                },
                            ])
                            ->helperText('Keep this as a JSON object matching the selected public Vue page contract.')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Source and review')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('owner_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_url')
                            ->url()
                            ->maxLength(500),
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
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('component')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner_label')
                    ->label('Owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
                Tables\Columns\TextColumn::make('review_due_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('slug')
            ->filters([
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
            'index' => Pages\ListPublicPages::route('/'),
            'create' => Pages\CreatePublicPage::route('/create'),
            'view' => Pages\ViewPublicPage::route('/{record}'),
            'edit' => Pages\EditPublicPage::route('/{record}/edit'),
        ];
    }
}

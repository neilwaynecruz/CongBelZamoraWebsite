<?php

namespace App\Filament\Resources;

use App\Enums\SourceType;
use App\Filament\Resources\SourceRecordResource\Pages;
use App\Filament\Support\SoftDeletableResource;
use App\Models\SourceRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SourceRecordResource extends Resource
{
    use SoftDeletableResource;

    protected static ?string $model = SourceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Content Governance';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'url'];
    }

    /**
     * @return Builder<SourceRecord>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['contentOwner', 'verifier', 'sourceable']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Source')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Select::make('source_type')
                            ->options(self::sourceTypeOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('source_tier')
                            ->options([
                                'tier_1' => 'Tier 1 - Primary official',
                                'tier_2' => 'Tier 2 - Reliable secondary',
                                'tier_3' => 'Tier 3 - Discovery only',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('public_status')
                            ->maxLength(80),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Ownership and review')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('content_owner_id')
                            ->label('Content owner')
                            ->relationship('contentOwner', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('verified_by_id')
                            ->label('Verified by')
                            ->relationship('verifier', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('last_reviewed_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('next_review_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('effective_at')
                            ->timezone('Asia/Manila'),
                    ]),
                Forms\Components\Section::make('Related record')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('sourceable_display')
                            ->label('Attached to')
                            ->formatStateUsing(fn (?SourceRecord $record): string => self::relatedRecordLabel($record))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\KeyValue::make('metadata')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('internal_notes')
                            ->rows(4)
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
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('source_type')
                    ->formatStateUsing(fn (?SourceType $state): string => $state?->label() ?? 'Unspecified')
                    ->badge(),
                Tables\Columns\TextColumn::make('source_tier')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('related_record')
                    ->label('Attached to')
                    ->getStateUsing(fn (SourceRecord $record): string => self::relatedRecordLabel($record))
                    ->wrap(),
                Tables\Columns\TextColumn::make('contentOwner.name')
                    ->label('Owner')
                    ->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('next_review_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable()
                    ->color(fn (?string $state, SourceRecord $record): string => $record->next_review_at !== null && $record->next_review_at->isPast() ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('next_review_at')
            ->filters([
                Tables\Filters\Filter::make('needs_review')
                    ->label('Due for review')
                    ->query(fn (Builder $query): Builder => $query->needsReview()),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now())),
                Tables\Filters\SelectFilter::make('source_type')
                    ->options(self::sourceTypeOptions()),
                Tables\Filters\SelectFilter::make('source_tier')
                    ->options([
                        'tier_1' => 'Tier 1',
                        'tier_2' => 'Tier 2',
                        'tier_3' => 'Tier 3',
                    ]),
                Tables\Filters\SelectFilter::make('content_owner_id')
                    ->label('Owner')
                    ->relationship('contentOwner', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_sources')
                    ->label('Export sources CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => self::exportSourcesCsv(self::getEloquentQuery())),
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
            'index' => Pages\ManageSourceRecords::route('/'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function sourceTypeOptions(): array
    {
        return collect(SourceType::cases())
            ->mapWithKeys(fn (SourceType $type): array => [$type->value => $type->label()])
            ->all();
    }

    private static function relatedRecordLabel(?SourceRecord $record): string
    {
        if (! $record instanceof SourceRecord || ! $record->sourceable instanceof Model) {
            return 'Global / unattached source';
        }

        foreach (['title', 'public_title', 'official_title', 'label', 'name', 'reference_code', 'bill_number'] as $attribute) {
            $value = $record->sourceable->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return str(class_basename($record->sourceable))->headline().': '.$value;
            }
        }

        return str(class_basename($record->sourceable))->headline().' #'.$record->sourceable->getKey();
    }

    /**
     * @param  Builder<SourceRecord>  $query
     */
    private static function exportSourcesCsv(Builder $query): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'source-records-'.now('Asia/Manila')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['title', 'url', 'source_type', 'source_tier', 'public_status', 'owner', 'verified_by', 'verified_at', 'next_review_at', 'expires_at']);

            $query->with(['contentOwner', 'verifier'])->orderBy('title')->chunk(200, function ($sources) use ($handle): void {
                foreach ($sources as $source) {
                    /** @var SourceRecord $source */
                    fputcsv($handle, [
                        $source->title,
                        $source->url,
                        $source->source_type?->value,
                        $source->source_tier,
                        $source->public_status,
                        $source->contentOwner?->name,
                        $source->verifier?->name,
                        $source->verified_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $source->next_review_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $source->expires_at?->timezone('Asia/Manila')->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

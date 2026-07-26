<?php

namespace App\Filament\Resources;

use App\Enums\ContentApprovalStatus;
use App\Filament\Resources\ContentApprovalResource\Pages;
use App\Models\ContentApproval;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ContentApprovalResource extends Resource
{
    protected static ?string $model = ContentApproval::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Content Governance';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * @return Builder<ContentApproval>
     */
    public static function getEloquentQuery(): Builder
    {
        return ContentApproval::query()
            ->with(['approvable', 'submitter', 'reviewer'])
            ->latest('submitted_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Approval decision')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('approval_item')
                            ->label('Approval item')
                            ->formatStateUsing(fn (?ContentApproval $record): string => self::approvalItem($record))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options(ContentApprovalStatus::options())
                            ->required()
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\Toggle::make('is_fast_track')
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\Select::make('reviewed_by_id')
                            ->label('Reviewer')
                            ->relationship('reviewer', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->timezone('Asia/Manila')
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->timezone('Asia/Manila')
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->timezone('Asia/Manila')
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                    ]),
                Forms\Components\Section::make('Human review checklist')
                    ->description('Complete every checklist item before marking launch approvals as approved.')
                    ->schema([
                        Forms\Components\Textarea::make('checklist_display')
                            ->label('Checklist')
                            ->formatStateUsing(fn (?ContentApproval $record): string => self::checklistText($record))
                            ->rows(7)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('review_notes')
                            ->rows(5)
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->rows(4)
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                    ]),
                Forms\Components\Section::make('Record details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('approvable_display')
                            ->label('Related record')
                            ->formatStateUsing(fn (?ContentApproval $record): string => self::relatedRecordLabel($record))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('revision_number')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\Select::make('submitted_by_id')
                            ->label('Submitted by')
                            ->relationship('submitter', 'name')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                        Forms\Components\KeyValue::make('metadata')
                            ->columnSpanFull()
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('launch_item')
                    ->label('Approval item')
                    ->getStateUsing(fn (ContentApproval $record): string => self::approvalItem($record))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('metadata->launch_item', 'like', "%{$search}%");
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('related_record')
                    ->label('Related record')
                    ->getStateUsing(fn (ContentApproval $record): string => self::relatedRecordLabel($record))
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (ContentApprovalStatus $state): string => $state->label())
                    ->badge()
                    ->color(fn (ContentApprovalStatus $state): string => match ($state) {
                        ContentApprovalStatus::ForReview => 'warning',
                        ContentApprovalStatus::NeedsRevision,
                        ContentApprovalStatus::Rejected => 'danger',
                        ContentApprovalStatus::Approved,
                        ContentApprovalStatus::Published => 'success',
                        ContentApprovalStatus::Scheduled => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitter.name')
                    ->label('Submitted by')
                    ->placeholder('Not recorded'),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->placeholder('Not reviewed'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable()
                    ->placeholder('Pending'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ContentApprovalStatus::options()),
                Tables\Filters\Filter::make('launch_gate')
                    ->label('Launch gates only')
                    ->query(fn (Builder $query): Builder => $query->where('metadata->launch_gate', true)),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && ! in_array($record->status, [ContentApprovalStatus::Approved, ContentApprovalStatus::Published], true))
                    ->requiresConfirmation()
                    ->action(function (ContentApproval $record): void {
                        $record->forceFill([
                            'status' => ContentApprovalStatus::Approved,
                            'reviewed_by_id' => Auth::id(),
                            'reviewed_at' => now(),
                            'approved_at' => now(),
                        ])->save();
                    }),
                Tables\Actions\Action::make('needs_revision')
                    ->label('Needs revision')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && $record->status !== ContentApprovalStatus::NeedsRevision)
                    ->requiresConfirmation()
                    ->action(function (ContentApproval $record): void {
                        $record->forceFill([
                            'status' => ContentApprovalStatus::NeedsRevision,
                            'reviewed_by_id' => Auth::id(),
                            'reviewed_at' => now(),
                        ])->save();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)),
            ])
            ->bulkActions([]);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentApprovals::route('/'),
            'view' => Pages\ViewContentApproval::route('/{record}'),
            'edit' => Pages\EditContentApproval::route('/{record}/edit'),
        ];
    }

    private static function canUpdateApproval(?ContentApproval $record): bool
    {
        $user = Auth::user();

        return $record !== null
            && $user instanceof User
            && $user->can('update', $record);
    }

    private static function approvalItem(?ContentApproval $record): string
    {
        if (! $record instanceof ContentApproval) {
            return '';
        }

        $launchItem = data_get($record->metadata, 'launch_item');

        return is_string($launchItem) && $launchItem !== ''
            ? $launchItem
            : self::relatedRecordLabel($record);
    }

    private static function checklistText(?ContentApproval $record): string
    {
        if (! $record instanceof ContentApproval) {
            return '';
        }

        $items = data_get($record->metadata, 'checklist', []);

        if (! is_array($items) || $items === []) {
            return 'No checklist recorded for this approval.';
        }

        return collect($items)
            ->values()
            ->map(fn (mixed $item, int $index): string => ($index + 1).'. '.(string) $item)
            ->implode(PHP_EOL);
    }

    private static function relatedRecordLabel(ContentApproval $record): string
    {
        $approvable = $record->approvable;

        if (! $approvable instanceof Model) {
            return 'Unlinked approval #'.$record->getKey();
        }

        foreach (['title', 'public_title', 'official_title', 'label', 'name', 'reference_code', 'bill_number'] as $attribute) {
            $value = $approvable->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return str(class_basename($approvable))->headline().': '.$value;
            }
        }

        return str(class_basename($approvable))->headline().' #'.$approvable->getKey();
    }
}

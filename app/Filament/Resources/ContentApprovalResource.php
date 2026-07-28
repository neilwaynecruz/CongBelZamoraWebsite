<?php

namespace App\Filament\Resources;

use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Events\ContentApprovalDecided;
use App\Events\ContentApprovalSubmitted;
use App\Filament\Resources\ContentApprovalResource\Pages;
use App\Models\ContentApproval;
use App\Models\ContentSnapshot;
use App\Models\User;
use App\Services\ContentGovernanceService;
use App\Services\ContentPublicationService;
use App\Services\ContentSnapshotService;
use App\Support\ContentGovernanceException;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ContentApprovalResource extends Resource
{
    protected static ?string $model = ContentApproval::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Content Governance';

    protected static ?string $navigationLabel = 'Content Approvals';

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
            ->with(['approvable', 'submitter', 'reviewer', 'snapshots.capturedBy'])
            ->latest('submitted_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Workflow')
                    ->description('Recommended path: Draft → For review → Approved → Published. Use Needs revision or Rejected when content must change.')
                    ->schema([
                        Forms\Components\Placeholder::make('workflow_hint')
                            ->label('Current stage')
                            ->content(fn (?ContentApproval $record): string => $record?->status?->label() ?? 'Unknown'),
                    ]),
                Forms\Components\Section::make('Governance baseline')
                    ->description(fn (): string => (string) config('belzamora.governance.content_approval.policy_note'))
                    ->schema([
                        Forms\Components\Placeholder::make('governance_readiness')
                            ->label('Publish readiness')
                            ->content(fn (?ContentApproval $record): HtmlString => new HtmlString(self::governanceChecklistHtml($record))),
                        Forms\Components\Placeholder::make('version_compare')
                            ->label('Current draft vs last published version')
                            ->content(fn (?ContentApproval $record): HtmlString => new HtmlString(self::compareHtml($record))),
                    ]),
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
                        Forms\Components\DateTimePicker::make('scheduled_for')
                            ->timezone('Asia/Manila')
                            ->helperText('Used when status is Scheduled. The scheduler publishes due records every 15 minutes.')
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->timezone('Asia/Manila')
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('End of validity')
                            ->timezone('Asia/Manila')
                            ->helperText('Optional. Use for advisories, deadlines, or temporary notices that should not remain publicly trusted after a known date.')
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\DateTimePicker::make('unpublished_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
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
                        Forms\Components\Textarea::make('change_summary')
                            ->label('Change summary')
                            ->helperText('Required before schedule or publish. Summarize what changed and why this version is ready.')
                            ->rows(4)
                            ->disabled(fn (?ContentApproval $record): bool => ! self::canUpdateApproval($record)),
                        Forms\Components\Textarea::make('archive_reason')
                            ->label('Archive / unpublish reason')
                            ->rows(3)
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Version history')
                    ->description('Snapshots are normalized editorial records captured during review, approval, publishing, archive, and rollback actions.')
                    ->schema([
                        Forms\Components\Placeholder::make('snapshot_history')
                            ->label('Captured versions')
                            ->content(fn (?ContentApproval $record): HtmlString => new HtmlString(self::snapshotHistoryHtml($record))),
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
                Tables\Columns\TextColumn::make('snapshots_count')
                    ->counts('snapshots')
                    ->label('Versions')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('freshness')
                    ->label('Freshness')
                    ->getStateUsing(fn (ContentApproval $record): string => self::freshnessLabel($record))
                    ->badge()
                    ->color(fn (ContentApproval $record): string => self::freshnessColor($record)),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ContentApprovalStatus::options()),
                Tables\Filters\Filter::make('my_queue')
                    ->label('Open review queue')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', [
                        ContentApprovalStatus::ForReview->value,
                        ContentApprovalStatus::NeedsRevision->value,
                    ])),
                Tables\Filters\Filter::make('submitted_by_me')
                    ->label('Submitted by me')
                    ->query(fn (Builder $query): Builder => $query->where('submitted_by_id', Auth::id())),
                Tables\Filters\Filter::make('launch_gate')
                    ->label('Launch gates only')
                    ->query(fn (Builder $query): Builder => $query->where('metadata->launch_gate', true)),
                Tables\Filters\Filter::make('stale_or_due')
                    ->label('Stale or due for review')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->where('valid_until', '<=', now())
                            ->orWhereHasMorph('approvable', self::governedModelClasses(), function (Builder $query): void {
                                $query->where('review_due_at', '<=', now());
                            });
                    })),
            ])
            ->actions([
                Tables\Actions\Action::make('submit_for_review')
                    ->label('Submit for review')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && in_array($record->status, [
                            ContentApprovalStatus::Draft,
                            ContentApprovalStatus::NeedsRevision,
                        ], true))
                    ->form([
                        Forms\Components\Textarea::make('change_summary')
                            ->label('Change summary')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (ContentApproval $record, array $data): void {
                        $actor = Auth::user();

                        $record->forceFill([
                            'status' => ContentApprovalStatus::ForReview,
                            'submitted_by_id' => $record->submitted_by_id ?? Auth::id(),
                            'submitted_at' => $record->submitted_at ?? now(),
                            'change_summary' => $data['change_summary'],
                            'rejection_reason' => null,
                        ])->save();

                        app(ContentSnapshotService::class)->capture(
                            $record->fresh(['approvable']) ?? $record,
                            ContentSnapshotType::Submitted,
                            $actor instanceof User ? $actor : null,
                            $data['change_summary'],
                        );

                        ContentApprovalSubmitted::dispatch(
                            $record->fresh() ?? $record,
                            $actor instanceof User ? $actor : null,
                        );
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && ! in_array($record->status, [
                            ContentApprovalStatus::Approved,
                            ContentApprovalStatus::Published,
                        ], true))
                    ->form([
                        Forms\Components\Textarea::make('change_summary')
                            ->label('Approval change summary')
                            ->default(fn (ContentApproval $record): ?string => $record->change_summary)
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (ContentApproval $record, array $data): void {
                        $actor = Auth::user();

                        $record->forceFill([
                            'status' => ContentApprovalStatus::Approved,
                            'reviewed_by_id' => Auth::id(),
                            'reviewed_at' => now(),
                            'approved_at' => now(),
                            'change_summary' => $data['change_summary'],
                            'rejection_reason' => null,
                        ])->save();

                        app(ContentSnapshotService::class)->capture(
                            $record->fresh(['approvable']) ?? $record,
                            ContentSnapshotType::Approved,
                            $actor instanceof User ? $actor : null,
                            $data['change_summary'],
                        );

                        ContentApprovalDecided::dispatch(
                            $record->fresh() ?? $record,
                            ContentApprovalStatus::Approved,
                            $actor instanceof User ? $actor : null,
                        );
                    }),
                Tables\Actions\Action::make('schedule')
                    ->label('Schedule')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && in_array($record->status, [
                            ContentApprovalStatus::ForReview,
                            ContentApprovalStatus::Approved,
                            ContentApprovalStatus::Scheduled,
                        ], true))
                    ->form([
                        Forms\Components\DateTimePicker::make('scheduled_for')
                            ->label('Publish at')
                            ->timezone('Asia/Manila')
                            ->required()
                            ->after(now()->subMinute()),
                    ])
                    ->action(function (ContentApproval $record, array $data): void {
                        $actor = Auth::user();

                        try {
                            app(ContentPublicationService::class)->schedule(
                                $record,
                                $data['scheduled_for'],
                                $actor instanceof User ? $actor : null,
                            );
                        } catch (ContentGovernanceException $exception) {
                            self::notifyGovernanceIssues($exception);
                        }
                    }),
                Tables\Actions\Action::make('publish')
                    ->label('Mark published')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && $record->status === ContentApprovalStatus::Approved)
                    ->requiresConfirmation()
                    ->action(function (ContentApproval $record): void {
                        $actor = Auth::user();

                        try {
                            app(ContentPublicationService::class)->publish(
                                $record,
                                $actor instanceof User ? $actor : null,
                            );
                        } catch (ContentGovernanceException $exception) {
                            self::notifyGovernanceIssues($exception);
                        }
                    }),
                Tables\Actions\Action::make('compare')
                    ->label('Compare')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('gray')
                    ->modalHeading('Current draft vs last published version')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (ContentApproval $record): HtmlString => new HtmlString(self::compareHtml($record, true))),
                Tables\Actions\Action::make('rollback')
                    ->label('Rollback')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record) && $record->snapshots()->exists())
                    ->form(fn (ContentApproval $record): array => [
                        Forms\Components\Select::make('snapshot_id')
                            ->label('Snapshot')
                            ->options(self::snapshotOptions($record))
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Rollback reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->action(function (ContentApproval $record, array $data): void {
                        $actor = Auth::user();
                        $snapshot = ContentSnapshot::query()->findOrFail($data['snapshot_id']);

                        try {
                            app(ContentPublicationService::class)->rollback(
                                $record,
                                $snapshot,
                                $actor instanceof User ? $actor : null,
                                $data['reason'],
                            );
                        } catch (\InvalidArgumentException $exception) {
                            Notification::make()
                                ->title('Rollback blocked')
                                ->body($exception->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('archive')
                    ->label('Archive / unpublish')
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && $record->status !== ContentApprovalStatus::Archived)
                    ->form([
                        Forms\Components\Select::make('archive_reason_key')
                            ->label('Reason')
                            ->options(self::archiveReasonOptions())
                            ->required(),
                        Forms\Components\Textarea::make('archive_note')
                            ->label('Additional note')
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (ContentApproval $record, array $data): void {
                        $actor = Auth::user();
                        $reason = self::archiveReasonLabel((string) $data['archive_reason_key']);

                        if (filled($data['archive_note'] ?? null)) {
                            $reason .= ': '.$data['archive_note'];
                        }

                        app(ContentPublicationService::class)->archive(
                            $record,
                            $reason,
                            $actor instanceof User ? $actor : null,
                        );
                    }),
                Tables\Actions\Action::make('needs_revision')
                    ->label('Needs revision')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && $record->status !== ContentApprovalStatus::NeedsRevision)
                    ->form([
                        Forms\Components\Textarea::make('review_notes')
                            ->label('Revision notes')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (ContentApproval $record, array $data): void {
                        $actor = Auth::user();

                        $record->forceFill([
                            'status' => ContentApprovalStatus::NeedsRevision,
                            'reviewed_by_id' => Auth::id(),
                            'reviewed_at' => now(),
                            'review_notes' => $data['review_notes'],
                        ])->save();

                        ContentApprovalDecided::dispatch(
                            $record->fresh() ?? $record,
                            ContentApprovalStatus::NeedsRevision,
                            $actor instanceof User ? $actor : null,
                        );
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ContentApproval $record): bool => self::canUpdateApproval($record)
                        && $record->status !== ContentApprovalStatus::Rejected)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (ContentApproval $record, array $data): void {
                        $actor = Auth::user();

                        $record->forceFill([
                            'status' => ContentApprovalStatus::Rejected,
                            'reviewed_by_id' => Auth::id(),
                            'reviewed_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ])->save();

                        ContentApprovalDecided::dispatch(
                            $record->fresh() ?? $record,
                            ContentApprovalStatus::Rejected,
                            $actor instanceof User ? $actor : null,
                        );
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

    private static function governanceChecklistHtml(?ContentApproval $record): string
    {
        if (! $record instanceof ContentApproval) {
            return '';
        }

        $items = app(ContentGovernanceService::class)->readinessChecklist($record);

        return collect($items)
            ->map(fn (string $item): string => '<div class="py-0.5">'.e($item).'</div>')
            ->implode('');
    }

    private static function notifyGovernanceIssues(ContentGovernanceException $exception): void
    {
        Notification::make()
            ->title('Governance checklist incomplete')
            ->body(collect($exception->issues())->map(fn (string $issue): string => '- '.$issue)->implode(PHP_EOL))
            ->danger()
            ->persistent()
            ->send();
    }

    private static function compareHtml(?ContentApproval $record, bool $full = false): string
    {
        if (! $record instanceof ContentApproval) {
            return '';
        }

        $diff = app(ContentSnapshotService::class)->compareCurrentToPublished($record);

        if ($diff === []) {
            return '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">No differences from the last published snapshot, or no published snapshot exists yet.</div>';
        }

        $limit = $full ? 30 : 8;

        $rows = collect($diff)
            ->take($limit)
            ->map(function (array $change, string $field): string {
                return '<tr>'
                    .'<td class="border-b border-gray-200 px-3 py-2 align-top font-semibold text-gray-900">'.e($field).'</td>'
                    .'<td class="border-b border-gray-200 px-3 py-2 align-top text-gray-600">'.e(self::displayValue($change['from'] ?? null)).'</td>'
                    .'<td class="border-b border-gray-200 px-3 py-2 align-top text-gray-900">'.e(self::displayValue($change['to'] ?? null)).'</td>'
                    .'</tr>';
            })
            ->implode('');

        $more = count($diff) > $limit
            ? '<p class="mt-3 text-xs text-gray-500">'.(count($diff) - $limit).' additional changed field(s) hidden in this compact preview.</p>'
            : '';

        return '<div class="overflow-x-auto rounded-lg border border-gray-200">'
            .'<table class="min-w-full text-left text-sm">'
            .'<thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-3 py-2">Field</th><th class="px-3 py-2">Published</th><th class="px-3 py-2">Current draft</th></tr></thead>'
            .'<tbody>'.$rows.'</tbody></table></div>'.$more;
    }

    private static function snapshotHistoryHtml(?ContentApproval $record): string
    {
        if (! $record instanceof ContentApproval) {
            return '';
        }

        $snapshots = $record->snapshots()->with('capturedBy')->limit(8)->get();

        if ($snapshots->isEmpty()) {
            return '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">No editorial snapshots have been captured yet.</div>';
        }

        return $snapshots
            ->map(function (ContentSnapshot $snapshot): string {
                $issues = $snapshot->is_publishable
                    ? '<span class="text-green-700">Publishable at capture</span>'
                    : '<span class="text-red-700">'.count($snapshot->governance_issues ?? []).' gate issue(s)</span>';

                return '<div class="border-b border-gray-200 py-3 last:border-b-0">'
                    .'<div class="flex flex-wrap items-center gap-2 text-sm">'
                    .'<span class="font-semibold text-gray-950">v'.$snapshot->version_number.'</span>'
                    .'<span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">'.e($snapshot->snapshot_type->label()).'</span>'
                    .'<span class="text-gray-500">'.e($snapshot->captured_at?->timezone('Asia/Manila')->format('M j, Y g:i A') ?? 'No capture date').'</span>'
                    .'</div>'
                    .'<div class="mt-1 text-xs text-gray-600">'.$issues.' · '.e($snapshot->capturedBy?->name ?? 'System').'</div>'
                    .'<div class="mt-1 text-sm text-gray-700">'.e($snapshot->change_summary ?: 'No change summary recorded.').'</div>'
                    .'</div>';
            })
            ->implode('');
    }

    private static function freshnessLabel(ContentApproval $record): string
    {
        $validUntil = $record->valid_until;

        if ($validUntil !== null && $validUntil->lte(now())) {
            return 'Validity expired';
        }

        $reviewDueAt = self::approvableReviewDueAt($record);

        if ($reviewDueAt !== null && $reviewDueAt->lte(now())) {
            return 'Review overdue';
        }

        $warningDays = (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7);

        if ($reviewDueAt !== null && $reviewDueAt->lte(now()->addDays($warningDays))) {
            return 'Review soon';
        }

        return 'Current';
    }

    private static function freshnessColor(ContentApproval $record): string
    {
        return match (self::freshnessLabel($record)) {
            'Validity expired',
            'Review overdue' => 'danger',
            'Review soon' => 'warning',
            default => 'success',
        };
    }

    /**
     * @return array<int, string>
     */
    private static function snapshotOptions(ContentApproval $record): array
    {
        return $record->snapshots()
            ->latest('version_number')
            ->get()
            ->mapWithKeys(fn (ContentSnapshot $snapshot): array => [
                $snapshot->id => 'v'.$snapshot->version_number.' - '.$snapshot->snapshot_type->label().' - '.($snapshot->captured_at?->timezone('Asia/Manila')->format('M j, Y g:i A') ?? 'No date'),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function archiveReasonOptions(): array
    {
        $reasons = config('belzamora.governance.content_approval.archive_reasons', []);

        return is_array($reasons) ? $reasons : [];
    }

    private static function archiveReasonLabel(string $key): string
    {
        return (string) data_get(self::archiveReasonOptions(), $key, $key);
    }

    /**
     * @return list<class-string<Model>>
     */
    private static function governedModelClasses(): array
    {
        $modules = config('belzamora.governance.modules', []);

        if (! is_array($modules)) {
            return [];
        }

        return collect($modules)
            ->map(fn (mixed $policy): mixed => is_array($policy) ? data_get($policy, 'model') : null)
            ->filter(fn (mixed $class): bool => is_string($class) && is_subclass_of($class, Model::class))
            ->values()
            ->all();
    }

    private static function approvableReviewDueAt(ContentApproval $record): mixed
    {
        $record->loadMissing('approvable');

        return $record->approvable instanceof Model
            ? $record->approvable->getAttribute('review_due_at')
            : null;
    }

    private static function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'None';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[array]';
        }

        return (string) $value;
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

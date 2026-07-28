<?php

namespace App\Filament\Resources;

use App\Enums\AdminRole;
use App\Enums\ContactPreference;
use App\Enums\InquiryCategory as InquiryCategoryEnum;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\InquiryCaseManagementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Inquiries';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * @return Builder<Inquiry>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = Inquiry::query()->with(['assignedOfficer', 'activities.actor']);
        $user = Auth::user();

        if (! $user instanceof User) {
            return $query->whereKey([]);
        }

        if ($user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ReadOnlyAuditor)) {
            return $query;
        }

        if ($user->hasAdminRole(AdminRole::InquiryOfficer)) {
            return $query->where(function (Builder $builder) use ($user): void {
                $builder
                    ->whereNull('assigned_officer_id')
                    ->orWhere('assigned_officer_id', $user->id);
            });
        }

        return $query->whereKey([]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Routing')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('reference_code')
                            ->label('Reference code')
                            ->disabled(),
                        Forms\Components\Select::make('priority')
                            ->options(InquiryPriority::options())
                            ->required()
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\Select::make('status')
                            ->options(InquiryStatus::options())
                            ->required()
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record))
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                $status = InquiryStatus::tryFrom((string) $state);

                                if ($status === InquiryStatus::Resolved && blank($get('resolved_at'))) {
                                    $now = now('Asia/Manila')->format('Y-m-d H:i:s');
                                    $set('resolved_at', $now);

                                    if (blank($get('responded_at'))) {
                                        $set('responded_at', $now);
                                        $set('first_responded_at', $now);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('assigned_officer_id')
                            ->label('Assigned officer')
                            ->relationship(
                                name: 'assignedOfficer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->where('admin_role', AdminRole::InquiryOfficer->value)
                                    ->where('is_active', true),
                            )
                            ->searchable()
                            ->preload()
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\DateTimePicker::make('assigned_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                        Forms\Components\TextInput::make('inquiry_category')
                            ->label('Category')
                            ->formatStateUsing(fn (mixed $state): string => self::formatInquiryCategory($state))
                            ->disabled(),
                        Forms\Components\TextInput::make('office_unit')
                            ->label('Office unit / routing owner')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('first_response_due_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Citizen-provided details')
                    ->description('These fields are encrypted at rest and must not be copied outside approved District Office workflows. List and export views intentionally omit them.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('citizen_name')
                            ->label('Name')
                            ->disabled(),
                        Forms\Components\TextInput::make('contact_preference')
                            ->formatStateUsing(fn (mixed $state): string => self::formatContactPreference($state))
                            ->disabled(),
                        Forms\Components\TextInput::make('citizen_email')
                            ->label('Email')
                            ->disabled(),
                        Forms\Components\TextInput::make('citizen_mobile')
                            ->label('Mobile')
                            ->disabled(),
                        Forms\Components\Textarea::make('message_body')
                            ->label('Message')
                            ->columnSpanFull()
                            ->rows(6)
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Officer response tools')
                    ->description('Internal notes and canned replies are for staff use. Do not paste government IDs, medical data, or other sensitive documents here.')
                    ->schema([
                        Forms\Components\Select::make('canned_reply')
                            ->label('Insert canned reply')
                            ->options(self::cannedReplyOptions())
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                if (! filled($state)) {
                                    return;
                                }

                                $replies = config('belzamora.inquiries.canned_replies', []);
                                $reply = is_array($replies) ? (string) ($replies[$state] ?? '') : '';

                                if ($reply === '') {
                                    return;
                                }

                                $existing = trim((string) $get('internal_notes'));
                                $set('internal_notes', $existing === '' ? $reply : $existing.PHP_EOL.PHP_EOL.$reply);
                                $set('canned_reply', null);
                            })
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Internal notes / reply draft')
                            ->rows(6)
                            ->columnSpanFull()
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                    ]),
                Forms\Components\Section::make('Privacy and lifecycle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('privacy_consent_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('retention_expires_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('responded_at')
                            ->timezone('Asia/Manila')
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\DateTimePicker::make('first_responded_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->timezone('Asia/Manila')
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\DateTimePicker::make('last_internal_activity_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('closed_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                        Forms\Components\TextInput::make('closed_reason')
                            ->formatStateUsing(fn (?string $state): string => self::closureReasonLabel($state))
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('overdue_reminder_sent_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('anonymized_at')
                            ->disabled(),
                        Forms\Components\KeyValue::make('routing_notes')
                            ->columnSpanFull()
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                    ]),
                Forms\Components\Section::make('Activity history')
                    ->description('Timeline entries are internal and redact citizen details from audit/notification channels.')
                    ->schema([
                        Forms\Components\Placeholder::make('activity_timeline')
                            ->label('Case timeline')
                            ->content(fn (?Inquiry $record): HtmlString => new HtmlString(self::activityTimelineHtml($record))),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_code')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inquiry_category')
                    ->label('Category')
                    ->formatStateUsing(fn (mixed $state): string => self::formatInquiryCategory($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (mixed $state): string => self::formatInquiryStatus($state))
                    ->badge()
                    ->color(fn (mixed $state): string => self::inquiryStatusColor($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->formatStateUsing(fn (mixed $state): string => self::formatInquiryPriority($state))
                    ->badge()
                    ->color(fn (mixed $state): string => self::inquiryPriorityColor($state))
                    ->sortable(),
                Tables\Columns\IconColumn::make('sla_overdue')
                    ->label('SLA')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->getStateUsing(fn (Inquiry $record): bool => self::isOverdue($record))
                    ->tooltip(fn (Inquiry $record): string => self::isOverdue($record)
                        ? 'Unanswered more than '.self::slaHours().' hours'
                        : 'Within '.self::slaHours().'-hour attention window'),
                Tables\Columns\TextColumn::make('assignedOfficer.name')
                    ->label('Assigned officer')
                    ->placeholder('Unassigned')
                    ->sortable(),
                Tables\Columns\TextColumn::make('office_unit')
                    ->label('Office unit')
                    ->placeholder('Unrouted')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('first_response_due_at')
                    ->label('First response due')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('retention_expires_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(InquiryStatus::options()),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(InquiryPriority::options()),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned')
                    ->query(fn (Builder $query): Builder => $query->whereNull('assigned_officer_id')),
                Tables\Filters\Filter::make('my_inquiries')
                    ->label('Assigned to me')
                    ->query(fn (Builder $query): Builder => $query->where('assigned_officer_id', Auth::id())),
                Tables\Filters\Filter::make('my_needs_attention')
                    ->label('Needs my attention')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('assigned_officer_id', Auth::id())
                        ->openCase()
                        ->where(function (Builder $builder): void {
                            $builder
                                ->whereNull('last_internal_activity_at')
                                ->orWhere('last_internal_activity_at', '<=', now()->subDay())
                                ->orWhere('first_response_due_at', '<=', now());
                        })),
                Tables\Filters\Filter::make('overdue_sla')
                    ->label('SLA overdue (>'.self::slaHours().'h)')
                    ->query(fn (Builder $query): Builder => self::applyOverdueConstraint($query)),
            ])
            ->actions([
                Tables\Actions\Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record))
                    ->form([
                        Forms\Components\Select::make('assigned_officer_id')
                            ->label('Assigned officer')
                            ->options(self::activeInquiryOfficerOptions())
                            ->searchable()
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label('Assignment note')
                            ->rows(3),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $assignee = User::query()->find($data['assigned_officer_id']);
                        $actor = Auth::user();

                        if ($assignee instanceof User) {
                            app(InquiryCaseManagementService::class)->assign(
                                inquiry: $record,
                                assignee: $assignee,
                                actor: $actor instanceof User ? $actor : null,
                                note: filled($data['note'] ?? null) ? (string) $data['note'] : null,
                            );
                        }
                    }),
                Tables\Actions\Action::make('assign_to_me')
                    ->label('Assign to me')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && Auth::id() !== null
                        && $record->assigned_officer_id !== Auth::id())
                    ->requiresConfirmation()
                    ->action(function (Inquiry $record): void {
                        $actor = Auth::user();

                        if ($actor instanceof User) {
                            app(InquiryCaseManagementService::class)->assign(
                                inquiry: $record,
                                assignee: $actor,
                                actor: $actor,
                                note: 'Assigned through Assign to me action.',
                            );
                        }
                    }),
                Tables\Actions\Action::make('mark_under_review')
                    ->label('Under review')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && $record->status === InquiryStatus::Pending)
                    ->action(function (Inquiry $record): void {
                        $actor = Auth::user();
                        app(InquiryCaseManagementService::class)->markUnderReview(
                            inquiry: $record,
                            actor: $actor instanceof User ? $actor : null,
                        );
                    }),
                Tables\Actions\Action::make('add_internal_note')
                    ->label('Add note')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record))
                    ->form([
                        Forms\Components\Select::make('template_key')
                            ->label('Template')
                            ->options(self::cannedReplyOptions())
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (! filled($state)) {
                                    return;
                                }

                                $replies = config('belzamora.inquiries.canned_replies', []);
                                $reply = is_array($replies) ? (string) ($replies[$state] ?? '') : '';

                                if ($reply !== '') {
                                    $set('note', $reply);
                                }
                            }),
                        Forms\Components\Textarea::make('note')
                            ->label('Internal note / response draft')
                            ->required()
                            ->rows(5),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $actor = Auth::user();

                        if ($actor instanceof User) {
                            app(InquiryCaseManagementService::class)->addInternalNote(
                                inquiry: $record,
                                actor: $actor,
                                note: (string) $data['note'],
                                templateKey: filled($data['template_key'] ?? null) ? (string) $data['template_key'] : null,
                            );
                        }
                    }),
                Tables\Actions\Action::make('mark_responded')
                    ->label('Record response')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && $record->responded_at === null
                        && ! in_array($record->status, [
                            InquiryStatus::Closed,
                            InquiryStatus::Anonymized,
                        ], true))
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Response milestone note')
                            ->rows(4),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $actor = Auth::user();

                        if ($actor instanceof User) {
                            app(InquiryCaseManagementService::class)->markResponded(
                                inquiry: $record,
                                actor: $actor,
                                note: filled($data['note'] ?? null) ? (string) $data['note'] : null,
                            );
                        }
                    }),
                Tables\Actions\Action::make('mark_resolved')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && ! in_array($record->status, [
                            InquiryStatus::Resolved,
                            InquiryStatus::Closed,
                            InquiryStatus::Anonymized,
                        ], true))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Resolution note')
                            ->rows(4),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $actor = Auth::user();

                        if ($actor instanceof User) {
                            app(InquiryCaseManagementService::class)->resolve(
                                inquiry: $record,
                                actor: $actor,
                                note: filled($data['note'] ?? null) ? (string) $data['note'] : null,
                            );
                        }
                    }),
                Tables\Actions\Action::make('mark_closed')
                    ->label('Close')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && $record->status === InquiryStatus::Resolved)
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('closed_reason')
                            ->label('Closure reason')
                            ->options(self::closureReasonOptions())
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label('Closure note')
                            ->rows(4),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $actor = Auth::user();

                        if ($actor instanceof User) {
                            app(InquiryCaseManagementService::class)->close(
                                inquiry: $record,
                                actor: $actor,
                                reason: (string) $data['closed_reason'],
                                note: filled($data['note'] ?? null) ? (string) $data['note'] : null,
                            );
                        }
                    }),
                Tables\Actions\Action::make('reopen')
                    ->label('Reopen')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && in_array($record->status, [
                            InquiryStatus::Resolved,
                            InquiryStatus::Closed,
                        ], true))
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Reopen reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $actor = Auth::user();

                        if ($actor instanceof User) {
                            app(InquiryCaseManagementService::class)->reopen(
                                inquiry: $record,
                                actor: $actor,
                                note: (string) $data['note'],
                            );
                        }
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)),
            ])
            ->bulkActions([]);
    }

    /**
     * @param  Builder<Inquiry>  $query
     */
    public static function exportRedactedCsv(Builder $query): StreamedResponse
    {
        $filename = 'inquiries-redacted-'.now('Asia/Manila')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'reference_code',
                'inquiry_category',
                'status',
                'priority',
                'office_unit',
                'assigned_officer',
                'sla_overdue',
                'first_response_due_at',
                'created_at',
                'responded_at',
                'first_responded_at',
                'resolved_at',
                'closed_at',
                'closed_reason',
                'retention_expires_at',
            ]);

            $query->with('assignedOfficer')->orderByDesc('created_at')->chunk(200, function ($inquiries) use ($handle): void {
                foreach ($inquiries as $inquiry) {
                    /** @var Inquiry $inquiry */
                    fputcsv($handle, [
                        $inquiry->reference_code,
                        $inquiry->inquiry_category->value,
                        $inquiry->status->value,
                        $inquiry->priority->value,
                        $inquiry->office_unit,
                        $inquiry->assignedOfficer?->name,
                        self::isOverdue($inquiry) ? 'yes' : 'no',
                        $inquiry->first_response_due_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->created_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->responded_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->first_responded_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->resolved_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->closed_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->closed_reason,
                        $inquiry->retention_expires_at?->timezone('Asia/Manila')->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'view' => Pages\ViewInquiry::route('/{record}'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function cannedReplyOptions(): array
    {
        $replies = config('belzamora.inquiries.canned_replies', []);

        if (! is_array($replies)) {
            return [];
        }

        return collect($replies)
            ->mapWithKeys(fn (mixed $text, mixed $key): array => [
                (string) $key => str((string) $key)->replace('_', ' ')->headline()->toString(),
            ])
            ->all();
    }

    private static function canUpdateInquiry(?Inquiry $record): bool
    {
        $user = Auth::user();

        return $record !== null
            && $user instanceof User
            && $user->can('update', $record);
    }

    private static function isOverdue(Inquiry $record): bool
    {
        if ($record->first_responded_at !== null || $record->responded_at !== null || $record->resolved_at !== null) {
            return false;
        }

        if (! in_array($record->status, [InquiryStatus::Pending, InquiryStatus::UnderReview], true)) {
            return false;
        }

        return ($record->first_response_due_at !== null && $record->first_response_due_at->lte(now()))
            || ($record->created_at !== null && $record->created_at->lte(now()->subHours(self::slaHours())));
    }

    /**
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    private static function applyOverdueConstraint(Builder $query): Builder
    {
        return $query
            ->whereNull('responded_at')
            ->whereNull('first_responded_at')
            ->whereNull('resolved_at')
            ->whereIn('status', [
                InquiryStatus::Pending->value,
                InquiryStatus::UnderReview->value,
            ])
            ->where(function (Builder $builder): void {
                $builder
                    ->where('first_response_due_at', '<=', now())
                    ->orWhere('created_at', '<=', now()->subHours(self::slaHours()));
            });
    }

    private static function slaHours(): int
    {
        return max(1, (int) config('belzamora.inquiries.unanswered_sla_hours', 48));
    }

    private static function formatInquiryCategory(mixed $state): string
    {
        $category = $state instanceof InquiryCategoryEnum
            ? $state
            : InquiryCategoryEnum::tryFrom((string) $state);

        return $category?->label() ?? '';
    }

    private static function formatContactPreference(mixed $state): string
    {
        $preference = $state instanceof ContactPreference
            ? $state
            : ContactPreference::tryFrom((string) $state);

        return $preference?->label() ?? '';
    }

    private static function formatInquiryStatus(mixed $state): string
    {
        $status = self::inquiryStatusFrom($state);

        return $status?->label() ?? '';
    }

    private static function formatInquiryPriority(mixed $state): string
    {
        $priority = $state instanceof InquiryPriority
            ? $state
            : InquiryPriority::tryFrom((string) $state);

        return $priority?->label() ?? '';
    }

    private static function inquiryPriorityColor(mixed $state): string
    {
        return match ($state instanceof InquiryPriority ? $state : InquiryPriority::tryFrom((string) $state)) {
            InquiryPriority::Urgent => 'danger',
            InquiryPriority::High => 'warning',
            InquiryPriority::Normal => 'info',
            InquiryPriority::Low => 'gray',
            null => 'gray',
        };
    }

    /**
     * @return array<int, string>
     */
    private static function activeInquiryOfficerOptions(): array
    {
        return User::query()
            ->where('is_active', true)
            ->whereIn('admin_role', [
                AdminRole::SuperAdministrator->value,
                AdminRole::InquiryOfficer->value,
            ])
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function closureReasonOptions(): array
    {
        $reasons = config('belzamora.inquiries.case_management.closure_reasons', []);

        return is_array($reasons)
            ? collect($reasons)->mapWithKeys(fn (mixed $label, mixed $key): array => [(string) $key => (string) $label])->all()
            : [];
    }

    private static function closureReasonLabel(?string $state): string
    {
        if (! filled($state)) {
            return '';
        }

        return self::closureReasonOptions()[$state] ?? $state;
    }

    private static function activityTimelineHtml(?Inquiry $record): string
    {
        if (! $record instanceof Inquiry) {
            return '';
        }

        $activities = $record->activities()->with('actor')->limit(12)->get();

        if ($activities->isEmpty()) {
            return '<p class="text-sm text-gray-500">No activity recorded yet.</p>';
        }

        return $activities
            ->map(function ($activity): string {
                $actor = $activity->actor?->name ?? 'System';
                $date = $activity->created_at?->timezone('Asia/Manila')->format('M j, Y g:i A') ?? 'Unknown time';
                $status = $activity->old_status !== null || $activity->new_status !== null
                    ? '<div class="text-xs text-gray-500">Status: '.e((string) $activity->old_status ?: 'none').' -> '.e((string) $activity->new_status ?: 'none').'</div>'
                    : '';

                return '<div class="border-b border-gray-200 py-3 last:border-b-0">'
                    .'<div class="flex flex-wrap items-center justify-between gap-2">'
                    .'<span class="font-semibold text-gray-900">'.e($activity->type->label()).'</span>'
                    .'<span class="text-xs text-gray-500">'.e($date).'</span>'
                    .'</div>'
                    .'<div class="mt-1 text-sm text-gray-600">'.e($actor).'</div>'
                    .($activity->body !== null ? '<div class="mt-2 text-sm text-gray-700">'.nl2br(e($activity->body)).'</div>' : '')
                    .$status
                    .'</div>';
            })
            ->implode('');
    }

    private static function inquiryStatusColor(mixed $state): string
    {
        return match (self::inquiryStatusFrom($state)) {
            InquiryStatus::Pending => 'danger',
            InquiryStatus::UnderReview => 'warning',
            InquiryStatus::Resolved => 'success',
            InquiryStatus::Closed => 'gray',
            InquiryStatus::Anonymized => 'info',
            null => 'gray',
        };
    }

    private static function inquiryStatusFrom(mixed $state): ?InquiryStatus
    {
        return $state instanceof InquiryStatus
            ? $state
            : InquiryStatus::tryFrom((string) $state);
    }
}

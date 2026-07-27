<?php

namespace App\Filament\Resources;

use App\Enums\AdminRole;
use App\Enums\ContactPreference;
use App\Enums\InquiryCategory as InquiryCategoryEnum;
use App\Enums\InquiryStatus;
use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use App\Models\User;
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
        $query = Inquiry::query()->with('assignedOfficer');
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
                        Forms\Components\TextInput::make('inquiry_category')
                            ->label('Category')
                            ->formatStateUsing(fn (mixed $state): string => self::formatInquiryCategory($state))
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
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->timezone('Asia/Manila')
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\DateTimePicker::make('anonymized_at')
                            ->disabled(),
                        Forms\Components\KeyValue::make('routing_notes')
                            ->columnSpanFull()
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
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
                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned')
                    ->query(fn (Builder $query): Builder => $query->whereNull('assigned_officer_id')),
                Tables\Filters\Filter::make('my_inquiries')
                    ->label('Assigned to me')
                    ->query(fn (Builder $query): Builder => $query->where('assigned_officer_id', Auth::id())),
                Tables\Filters\Filter::make('overdue_sla')
                    ->label('SLA overdue (>'.self::slaHours().'h)')
                    ->query(fn (Builder $query): Builder => self::applyOverdueConstraint($query)),
            ])
            ->actions([
                Tables\Actions\Action::make('assign_to_me')
                    ->label('Assign to me')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && Auth::id() !== null
                        && $record->assigned_officer_id !== Auth::id())
                    ->requiresConfirmation()
                    ->action(function (Inquiry $record): void {
                        $notes = is_array($record->routing_notes) ? $record->routing_notes : [];
                        $notes['assignment'] = [
                            'assigned_to_user_id' => Auth::id(),
                            'assigned_at' => now('Asia/Manila')->toIso8601String(),
                            'source' => 'admin_assign_to_me_action',
                        ];

                        $record->forceFill([
                            'assigned_officer_id' => Auth::id(),
                            'status' => $record->status === InquiryStatus::Pending
                                ? InquiryStatus::UnderReview
                                : $record->status,
                            'routing_notes' => $notes,
                        ])->save();
                    }),
                Tables\Actions\Action::make('mark_under_review')
                    ->label('Under review')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && $record->status === InquiryStatus::Pending)
                    ->action(function (Inquiry $record): void {
                        $record->forceFill([
                            'status' => InquiryStatus::UnderReview,
                            'assigned_officer_id' => $record->assigned_officer_id ?? Auth::id(),
                        ])->save();
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
                    ->action(function (Inquiry $record): void {
                        $record->forceFill([
                            'status' => InquiryStatus::Resolved,
                            'responded_at' => $record->responded_at ?? now(),
                            'resolved_at' => now(),
                        ])->save();
                    }),
                Tables\Actions\Action::make('mark_closed')
                    ->label('Close')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)
                        && $record->status === InquiryStatus::Resolved)
                    ->requiresConfirmation()
                    ->action(function (Inquiry $record): void {
                        $record->forceFill([
                            'status' => InquiryStatus::Closed,
                        ])->save();
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
                'assigned_officer',
                'sla_overdue',
                'created_at',
                'responded_at',
                'resolved_at',
                'retention_expires_at',
            ]);

            $query->with('assignedOfficer')->orderByDesc('created_at')->chunk(200, function ($inquiries) use ($handle): void {
                foreach ($inquiries as $inquiry) {
                    /** @var Inquiry $inquiry */
                    fputcsv($handle, [
                        $inquiry->reference_code,
                        $inquiry->inquiry_category->value,
                        $inquiry->status->value,
                        $inquiry->assignedOfficer?->name,
                        self::isOverdue($inquiry) ? 'yes' : 'no',
                        $inquiry->created_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->responded_at?->timezone('Asia/Manila')->toDateTimeString(),
                        $inquiry->resolved_at?->timezone('Asia/Manila')->toDateTimeString(),
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
        if ($record->responded_at !== null || $record->resolved_at !== null) {
            return false;
        }

        if (! in_array($record->status, [InquiryStatus::Pending, InquiryStatus::UnderReview], true)) {
            return false;
        }

        return $record->created_at !== null
            && $record->created_at->lte(now()->subHours(self::slaHours()));
    }

    /**
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    private static function applyOverdueConstraint(Builder $query): Builder
    {
        return $query
            ->whereNull('responded_at')
            ->whereNull('resolved_at')
            ->whereIn('status', [
                InquiryStatus::Pending->value,
                InquiryStatus::UnderReview->value,
            ])
            ->where('created_at', '<=', now()->subHours(self::slaHours()));
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

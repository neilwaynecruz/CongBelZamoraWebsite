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
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
            return $query->whereRaw('1 = 0');
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

        return $query->whereRaw('1 = 0');
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
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\Select::make('assigned_officer_id')
                            ->label('Assigned officer')
                            ->relationship(
                                name: 'assignedOfficer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->where('admin_role', AdminRole::InquiryOfficer->value),
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
                    ->description('These fields are encrypted at rest and must not be copied outside approved District Office workflows.')
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
                Forms\Components\Section::make('Privacy and lifecycle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('privacy_consent_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('retention_expires_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('responded_at')
                            ->disabled(fn (?Inquiry $record): bool => ! self::canUpdateInquiry($record)),
                        Forms\Components\DateTimePicker::make('resolved_at')
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
                Tables\Columns\TextColumn::make('assignedOfficer.name')
                    ->label('Assigned officer')
                    ->placeholder('Unassigned')
                    ->sortable(),
                Tables\Columns\TextColumn::make('retention_expires_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(InquiryStatus::options()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Inquiry $record): bool => self::canUpdateInquiry($record)),
            ])
            ->bulkActions([]);
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

    private static function canUpdateInquiry(?Inquiry $record): bool
    {
        $user = Auth::user();

        return $record !== null
            && $user instanceof User
            && $user->can('update', $record);
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

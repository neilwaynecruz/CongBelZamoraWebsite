<?php

namespace App\Filament\Resources;

use App\Enums\AdminRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Governance & Audit';

    protected static ?string $navigationLabel = 'Staff Users';

    protected static ?string $modelLabel = 'Staff user';

    protected static ?int $navigationSort = 80;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Staff account')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('admin_role')
                            ->label('Admin role')
                            ->options(AdminRole::options())
                            ->required()
                            ->native(false)
                            ->rules([
                                fn (?User $record): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                                    if (! $record instanceof User || Auth::id() !== $record->id) {
                                        return;
                                    }

                                    if (
                                        $record->hasAdminRole(AdminRole::SuperAdministrator)
                                        && (string) $value !== AdminRole::SuperAdministrator->value
                                    ) {
                                        $fail('You cannot remove your own Super Administrator role.');
                                    }
                                },
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active staff access')
                            ->helperText('Inactive staff cannot sign in to /admin.')
                            ->default(true)
                            ->disabled(fn (?User $record): bool => $record !== null && Auth::id() === $record->id),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rule(Password::defaults())
                            ->helperText(fn (string $operation): string => $operation === 'edit'
                                ? 'Leave blank to keep the current password. Filling this field resets access credentials.'
                                : 'Temporary password for first sign-in. Ask the staff member to change it after login.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('admin_role')
                    ->label('Role')
                    ->formatStateUsing(fn (?AdminRole $state): string => $state?->label() ?? 'No role')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->hasEnabledTwoFactorAuthentication())
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->placeholder('Not verified')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('admin_role')
                    ->label('Role')
                    ->options(AdminRole::options()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active access')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Deactivated only')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('two_factor_confirmed_at')
                    ->label('2FA status')
                    ->nullable()
                    ->trueLabel('2FA enabled')
                    ->falseLabel('2FA missing')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('two_factor_confirmed_at')->whereNotNull('two_factor_secret'),
                        false: fn (Builder $query): Builder => $query->where(function (Builder $inner): void {
                            $inner->whereNull('two_factor_confirmed_at')->orWhereNull('two_factor_secret');
                        }),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record): bool => self::canDeactivate($record))
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate staff access')
                    ->modalDescription('The user keeps their account record but loses /admin access until reactivated.')
                    ->action(function (User $record): void {
                        $remaining = User::query()
                            ->where('admin_role', AdminRole::SuperAdministrator->value)
                            ->where('is_active', true)
                            ->whereKeyNot($record->id)
                            ->exists();

                        if ($record->hasAdminRole(AdminRole::SuperAdministrator) && ! $remaining) {
                            Notification::make()
                                ->title('Cannot deactivate the last Super Administrator')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->forceFill(['is_active' => false])->save();

                        Notification::make()
                            ->title('Staff access deactivated')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => self::canManage() && ! $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill(['is_active' => true])->save();
                    }),
            ])
            ->bulkActions([]);
    }

    /**
     * @return Builder<User>
     */
    public static function getEloquentQuery(): Builder
    {
        return User::query()
            ->whereNotNull('admin_role');
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    private static function canManage(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->canManageStaffUsers();
    }

    private static function canDeactivate(User $record): bool
    {
        $actor = Auth::user();

        return $actor instanceof User
            && $actor->can('deactivate', $record)
            && $record->is_active;
    }
}

<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\AdminRole;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('deactivate')
                ->label('Deactivate')
                ->color('danger')
                ->visible(fn (): bool => $this->record instanceof User
                    && auth()->user()?->can('deactivate', $this->record)
                    && $this->record->is_active)
                ->requiresConfirmation()
                ->action(function (): void {
                    /** @var User $record */
                    $record = $this->record;
                    $this->guardLastActiveSuperAdministrator($record, deactivating: true);
                    $record->forceFill(['is_active' => false])->save();
                    $this->refreshFormData(['is_active']);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var User $record */
        $record = $this->record;

        if (auth()->id() === $record->id && array_key_exists('is_active', $data) && $data['is_active'] === false) {
            throw ValidationException::withMessages([
                'is_active' => 'You cannot deactivate your own staff account.',
            ]);
        }

        $nextRole = AdminRole::tryFrom((string) ($data['admin_role'] ?? ''));

        if (
            auth()->id() === $record->id
            && $record->hasAdminRole(AdminRole::SuperAdministrator)
            && $nextRole !== AdminRole::SuperAdministrator
        ) {
            throw ValidationException::withMessages([
                'admin_role' => 'You cannot remove your own Super Administrator role.',
            ]);
        }

        if (
            $record->hasAdminRole(AdminRole::SuperAdministrator)
            && $record->is_active
            && (
                ($nextRole !== AdminRole::SuperAdministrator)
                || (array_key_exists('is_active', $data) && $data['is_active'] === false)
            )
        ) {
            $this->guardLastActiveSuperAdministrator($record, deactivating: array_key_exists('is_active', $data) && $data['is_active'] === false);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var User $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'staff_user.updated',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'name' => $record->name,
                'email' => $record->email,
                'admin_role' => $record->admin_role?->value,
                'is_active' => $record->is_active,
                'password_reset' => filled($this->data['password'] ?? null),
            ],
        );
    }

    private function guardLastActiveSuperAdministrator(User $record, bool $deactivating = false): void
    {
        $remaining = User::query()
            ->where('admin_role', AdminRole::SuperAdministrator->value)
            ->where('is_active', true)
            ->whereKeyNot($record->id)
            ->exists();

        if ($remaining) {
            return;
        }

        throw ValidationException::withMessages([
            $deactivating ? 'is_active' : 'admin_role' => 'At least one active Super Administrator must remain.',
        ]);
    }
}

<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $data['email_verified_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'staff_user.created',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'name' => $record->name,
                'email' => $record->email,
                'admin_role' => $record->admin_role?->value,
                'is_active' => $record->is_active,
            ],
        );
    }
}

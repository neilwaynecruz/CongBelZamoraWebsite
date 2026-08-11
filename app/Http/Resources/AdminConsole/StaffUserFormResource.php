<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;

class StaffUserFormResource
{
    public function __construct(
        private readonly ?User $staffUser,
        private readonly string $mode,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $staffUser = $this->staffUser;

        return [
            'mode' => $this->mode,
            'title' => $staffUser instanceof User ? 'Edit '.$staffUser->name : 'Create staff user',
            'action' => $staffUser instanceof User
                ? route('admin-console.users.update', $staffUser)
                : route('admin-console.users.store'),
            'method' => $staffUser instanceof User ? 'put' : 'post',
            'backHref' => $staffUser instanceof User
                ? route('admin-console.users.show', $staffUser)
                : route('admin-console.users.index'),
            'indexHref' => route('admin-console.users.index'),
            'recordHref' => $staffUser instanceof User ? route('admin-console.users.show', $staffUser) : null,
            'values' => [
                'name' => $staffUser?->name ?? '',
                'email' => $staffUser?->email ?? '',
                'admin_role' => $staffUser?->admin_role?->value ?? '',
                'is_active' => $staffUser?->is_active ?? true,
                'password' => '',
            ],
            'options' => [
                'roles' => collect(AdminRole::cases())
                    ->map(fn (AdminRole $role): array => [
                        'value' => $role->value,
                        'label' => $role->label(),
                    ])
                    ->values()
                    ->all(),
            ],
            'passwordHelp' => $staffUser instanceof User
                ? 'Leave blank to keep the current password. Filling this field resets credentials and is audited.'
                : 'Create a temporary password, then ask the staff member to change it after first sign-in.',
            'twoFactorRequired' => (bool) config('belzamora.staff.require_2fa', false),
        ];
    }
}

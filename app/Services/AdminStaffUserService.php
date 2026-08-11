<?php

namespace App\Services;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AdminStaffUserService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor, Request $request): User
    {
        return DB::transaction(function () use ($payload, $actor, $request): User {
            $staffUser = new User;
            $staffUser->forceFill([
                ...$this->attributes($payload),
                'email_verified_at' => now(),
            ]);
            $staffUser->save();

            $this->auditLog->record(
                event: 'staff_user.created',
                auditable: $staffUser,
                actor: $actor,
                request: $request,
                newValues: $this->auditValues($staffUser, passwordChanged: true),
                metadata: ['custom_admin_console' => true],
            );

            return $staffUser;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(User $staffUser, array $payload, User $actor, Request $request): User
    {
        return DB::transaction(function () use ($staffUser, $payload, $actor, $request): User {
            $this->guardStaffMutation($staffUser, $payload, $actor);

            $before = $this->auditValues($staffUser, passwordChanged: false);
            $passwordChanged = array_key_exists('password', $payload);

            $staffUser->forceFill($this->attributes($payload));
            $staffUser->save();

            $this->auditLog->record(
                event: 'staff_user.updated',
                auditable: $staffUser,
                actor: $actor,
                request: $request,
                oldValues: $before,
                newValues: $this->auditValues($staffUser, $passwordChanged),
                metadata: ['custom_admin_console' => true],
            );

            return $staffUser;
        });
    }

    public function sendPasswordResetLink(User $staffUser, User $actor, Request $request): string
    {
        $this->guardPasswordReset($staffUser, $actor);

        $status = Password::broker()->sendResetLink([
            'email' => $staffUser->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        $this->auditLog->record(
            event: 'staff_user.password_reset_link_sent',
            auditable: $staffUser,
            actor: $actor,
            request: $request,
            newValues: [
                'email' => $staffUser->email,
                'admin_role' => $staffUser->admin_role?->value,
            ],
            metadata: ['custom_admin_console' => true],
        );

        return $status;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function attributes(array $payload): array
    {
        return Arr::only($payload, [
            'name',
            'email',
            'admin_role',
            'is_active',
            'password',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function guardStaffMutation(User $staffUser, array $payload, User $actor): void
    {
        $nextRole = AdminRole::tryFrom((string) ($payload['admin_role'] ?? ''));
        $nextActive = (bool) ($payload['is_active'] ?? false);

        if ($actor->is($staffUser) && ! $nextActive) {
            throw ValidationException::withMessages([
                'is_active' => 'You cannot deactivate your own staff account.',
            ]);
        }

        if (
            $actor->is($staffUser)
            && $staffUser->hasAdminRole(AdminRole::SuperAdministrator)
            && $nextRole !== AdminRole::SuperAdministrator
        ) {
            throw ValidationException::withMessages([
                'admin_role' => 'You cannot remove your own Super Administrator role.',
            ]);
        }

        if (
            $staffUser->hasAdminRole(AdminRole::SuperAdministrator)
            && $staffUser->is_active
            && ($nextRole !== AdminRole::SuperAdministrator || ! $nextActive)
        ) {
            $this->guardRemainingSuperAdministrator($staffUser, ! $nextActive);
        }
    }

    private function guardPasswordReset(User $staffUser, User $actor): void
    {
        if (! $actor->can('update', $staffUser)) {
            throw ValidationException::withMessages([
                'email' => 'You are not authorized to send staff password reset links.',
            ]);
        }

        if (! $staffUser->isStaff()) {
            throw ValidationException::withMessages([
                'email' => 'Password reset links can only be sent to staff users.',
            ]);
        }
    }

    private function guardRemainingSuperAdministrator(User $staffUser, bool $deactivating): void
    {
        $remaining = User::query()
            ->where('admin_role', AdminRole::SuperAdministrator->value)
            ->where('is_active', true)
            ->whereKeyNot($staffUser->id)
            ->exists();

        if ($remaining) {
            return;
        }

        throw ValidationException::withMessages([
            $deactivating ? 'is_active' : 'admin_role' => 'At least one active Super Administrator must remain.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function auditValues(User $staffUser, bool $passwordChanged): array
    {
        return [
            'name' => $staffUser->name,
            'email' => $staffUser->email,
            'admin_role' => $staffUser->admin_role?->value,
            'is_active' => $staffUser->is_active,
            'two_factor_enabled' => $staffUser->hasEnabledTwoFactorAuthentication(),
            'password_changed' => $passwordChanged,
        ];
    }
}

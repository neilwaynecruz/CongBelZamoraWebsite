<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin User
 */
class StaffUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $twoFactorRequired = (bool) config('belzamora.staff.require_2fa', false);
        $twoFactorEnabled = $this->hasEnabledTwoFactorAuthentication();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'href' => route('admin-console.users.show', $this->resource),
            'role' => [
                'value' => $this->admin_role?->value,
                'label' => $this->admin_role?->label() ?? 'No staff role',
            ],
            'isActive' => $this->is_active,
            'status' => [
                'value' => $this->is_active ? 'active' : 'deactivated',
                'label' => $this->is_active ? 'Active staff access' : 'Deactivated',
            ],
            'twoFactor' => [
                'required' => $twoFactorRequired,
                'enabled' => $twoFactorEnabled,
                'confirmedAt' => $this->formatDateTime($this->two_factor_confirmed_at),
                'status' => $twoFactorEnabled
                    ? 'Enabled'
                    : ($twoFactorRequired ? 'Required before admin access' : 'Not enabled'),
                'compliant' => ! $twoFactorRequired || $twoFactorEnabled,
            ],
            'emailVerifiedAt' => $this->formatDateTime($this->email_verified_at),
            'createdAt' => $this->formatDateTime($this->created_at),
            'updatedAt' => $this->formatDateTime($this->updated_at),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'deactivate' => $request->user()?->can('deactivate', $this->resource) ?? false,
                'sendPasswordReset' => $request->user()?->can('update', $this->resource) ?? false,
            ],
        ];
    }

    private function formatDateTime(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('M j, Y g:i A')
            : null;
    }
}

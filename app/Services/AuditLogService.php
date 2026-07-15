<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public function record(
        string $event,
        ?Model $auditable = null,
        ?User $actor = null,
        ?Request $request = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
    ): AuditLog {
        $actor ??= Auth::user();

        return AuditLog::query()->create([
            'actor_user_id' => $actor?->id,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'event' => $event,
            'ip_address_hash' => $request ? $this->hashNullable($request->ip()) : null,
            'user_agent_hash' => $request ? $this->hashNullable($request->userAgent()) : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    protected function hashNullable(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return hash_hmac('sha256', $value, (string) config('app.key'));
    }
}

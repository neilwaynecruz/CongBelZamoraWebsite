<?php

namespace App\Http\Resources\AdminConsole;

use App\Filament\Resources\AuditLogResource as FilamentAuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @mixin AuditLog
 */
class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $oldValues = $this->redactArray($this->old_values ?? []);
        $newValues = $this->redactArray($this->new_values ?? []);
        $metadata = $this->redactArray($this->metadata ?? []);

        return [
            'id' => $this->id,
            'event' => $this->event,
            'eventLabel' => Str::of($this->event)->replace(['.', '_'], ' ')->headline()->toString(),
            'href' => route('admin-console.audit.show', $this->resource),
            'actor' => [
                'id' => $this->actor?->id,
                'name' => $this->actor?->name ?? 'System',
                'email' => $this->actor?->email,
            ],
            'auditable' => [
                'type' => $this->auditable_type,
                'label' => $this->auditable_type !== null
                    ? Str::of(class_basename($this->auditable_type))->headline()->toString()
                    : 'System event',
                'id' => $this->auditable_id,
                'title' => $this->recordTitle(),
            ],
            'network' => [
                'ipAddressHash' => $this->ip_address_hash,
                'userAgentHash' => $this->user_agent_hash,
            ],
            'occurredAt' => $this->formatDateTime($this->occurred_at),
            'oldValues' => $oldValues,
            'newValues' => $newValues,
            'metadata' => $metadata,
            'diffRows' => $this->diffRows($oldValues, $newValues),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($request),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function redactArray(array $values): array
    {
        return collect($values)
            ->mapWithKeys(fn (mixed $value, string|int $key): array => [
                (string) $key => $this->redactValue((string) $key, $value),
            ])
            ->all();
    }

    private function redactValue(string $key, mixed $value): mixed
    {
        if ($this->isSensitiveKey($key)) {
            return '[REDACTED]';
        }

        if (is_array($value)) {
            return $this->redactArray($value);
        }

        return $value;
    }

    private function isSensitiveKey(string $key): bool
    {
        return Str::of($key)->lower()->contains([
            'password',
            'token',
            'secret',
            'recovery',
            'two_factor',
            'remember',
            'citizen_name',
            'citizen_email',
            'citizen_mobile',
            'message_body',
            'message',
            'private',
            'attachment',
            'file_path',
            'media_path',
            'document',
        ]);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @return list<array{key: string, old: mixed, new: mixed, changed: bool}>
     */
    private function diffRows(array $oldValues, array $newValues): array
    {
        $oldFlat = Arr::dot($oldValues);
        $newFlat = Arr::dot($newValues);

        return collect(array_unique([
            ...array_keys($oldFlat),
            ...array_keys($newFlat),
        ]))
            ->sort()
            ->map(fn (string $key): array => [
                'key' => $key,
                'old' => $oldFlat[$key] ?? null,
                'new' => $newFlat[$key] ?? null,
                'changed' => ($oldFlat[$key] ?? null) !== ($newFlat[$key] ?? null),
            ])
            ->values()
            ->all();
    }

    private function recordTitle(): string
    {
        $record = $this->resource->getRelationValue('auditable');

        if ($record === null) {
            return 'No bound record';
        }

        foreach (['title', 'public_title', 'official_title', 'name', 'label', 'reference_code', 'bill_number'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return class_basename($record).' #'.$record->getKey();
    }

    private function formatDateTime(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('M j, Y g:i A')
            : null;
    }

    private function filamentFallbackUrl(Request $request): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! ($request->user()?->can('view', $this->resource) ?? false)) {
            return null;
        }

        return FilamentAuditLogResource::getUrl('view', ['record' => $this->resource]);
    }
}

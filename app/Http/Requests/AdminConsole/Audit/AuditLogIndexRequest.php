<?php

namespace App\Http\Requests\AdminConsole\Audit;

use App\Models\AuditLog;
use Illuminate\Foundation\Http\FormRequest;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', AuditLog::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:80'],
            'event' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:255'],
            'actor' => ['nullable', 'regex:/^(system|\d+)$/'],
            'date_from' => ['nullable', 'date'],
            'date_until' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'in:occurred,event,type,actor,oldest'],
        ];
    }

    /**
     * @return array{
     *     search: string,
     *     event: string,
     *     type: string,
     *     actor: string,
     *     date_from: string,
     *     date_until: string,
     *     sort: string
     * }
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'event' => (string) ($validated['event'] ?? ''),
            'type' => (string) ($validated['type'] ?? ''),
            'actor' => filled($validated['actor'] ?? null) ? (string) $validated['actor'] : '',
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_until' => (string) ($validated['date_until'] ?? ''),
            'sort' => (string) ($validated['sort'] ?? 'occurred'),
        ];
    }
}

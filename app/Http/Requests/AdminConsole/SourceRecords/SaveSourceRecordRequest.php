<?php

namespace App\Http\Requests\AdminConsole\SourceRecords;

use App\Enums\SourceType;
use App\Models\SourceRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSourceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sourceRecord = $this->route('sourceRecord');

        return $sourceRecord instanceof SourceRecord
            ? $this->user()?->can('update', $sourceRecord) ?? false
            : $this->user()?->can('create', SourceRecord::class) ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'url' => ['nullable', 'url', 'max:500'],
            'source_type' => ['required', Rule::enum(SourceType::class)],
            'source_tier' => ['required', 'in:tier_1,tier_2,tier_3'],
            'public_status' => ['required', 'in:draft,published,archived,needs_review'],
            'description' => ['nullable', 'string', 'max:2000'],
            'content_owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'verified_by_id' => ['nullable', 'integer', 'exists:users,id'],
            'verified_at' => ['nullable', 'date'],
            'last_reviewed_at' => ['nullable', 'date'],
            'next_review_at' => ['nullable', 'date', 'after_or_equal:verified_at'],
            'effective_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:effective_at'],
            'internal_notes' => ['nullable', 'string', 'max:4000'],
            'metadata_json' => ['nullable', 'json'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $validated = $this->validated();
        $metadataJson = (string) ($validated['metadata_json'] ?? '');
        unset($validated['metadata_json']);

        return [
            ...$validated,
            'metadata' => filled($metadataJson) ? json_decode($metadataJson, true, flags: JSON_THROW_ON_ERROR) : null,
        ];
    }
}

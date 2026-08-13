<?php

namespace App\Http\Requests\AdminConsole\News;

use App\Enums\SourceType;
use App\Models\NewsRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class SaveNewsRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('newsRecord');

        if ($record instanceof NewsRecord) {
            return $this->user()?->can('update', $record) ?? false;
        }

        return $this->user()?->can('create', NewsRecord::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $record = $this->route('newsRecord');

        return [
            'news_category_id' => ['required', 'integer', 'exists:news_categories,id'],
            'news_status_id' => ['required', 'integer', 'exists:news_statuses,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('news_records', 'slug')->ignore($record instanceof NewsRecord ? $record->id : null),
            ],
            'published_at_label' => ['required', 'string', 'max:255'],
            'published_at' => ['required', 'date'],
            'public_updated_at_label' => ['required', 'string', 'max:255'],
            'public_updated_at' => ['nullable', 'date'],
            'source_label' => ['required', 'string', 'max:255'],
            'source_url' => ['required', 'url', 'max:500'],
            'summary' => ['required', 'string', 'max:5000'],
            'official_update' => ['required', 'string', 'max:5000'],
            'next_step' => ['required', 'string', 'max:2000'],
            'stale_note' => ['required', 'string', 'max:2000'],
            'owner_label' => ['required', 'string', 'max:255'],
            'source_verified_at' => ['required', 'date', 'before_or_equal:today'],
            'review_due_at' => ['required', 'date', 'after:today'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],

            'source_record' => ['required', 'array'],
            'source_record.title' => ['required', 'string', 'max:255'],
            'source_record.url' => ['required', 'url', 'max:500'],
            'source_record.source_type' => ['required', new Enum(SourceType::class)],
            'source_record.source_tier' => ['required', Rule::in(['tier_1', 'tier_2', 'tier_3'])],
            'source_record.public_status' => ['required', Rule::in(['draft', 'published', 'archived', 'needs_review'])],
            'source_record.description' => ['nullable', 'string', 'max:1000'],
            'source_record.verified_at' => ['required', 'date', 'before_or_equal:today'],
            'source_record.next_review_at' => ['required', 'date', 'after:today'],

            'media_assets' => ['array', 'max:20'],
            'media_assets.*.title' => ['required', 'string', 'max:255'],
            'media_assets.*.media_type' => ['required', Rule::in(['image', 'video', 'document', 'link'])],
            'media_assets.*.media_path' => ['nullable', 'string', 'max:255'],
            'media_assets.*.source_label' => ['required', 'string', 'max:255'],
            'media_assets.*.source_url' => ['required', 'url', 'max:500'],
            'media_assets.*.caption' => ['nullable', 'string', 'max:2000'],
            'media_assets.*.alt_text' => ['nullable', 'string', 'max:2000'],
            'media_assets.*.rights_note' => ['nullable', 'string', 'max:2000'],
            'media_assets.*.verified_at' => ['nullable', 'date', 'before_or_equal:today'],

            'approval' => ['required', 'array'],
            'approval.submit_for_review' => ['boolean'],
            'approval.change_summary' => ['nullable', 'required_if:approval.submit_for_review,true', 'string', 'max:1000'],
            'approval.is_fast_track' => ['boolean'],
            'approval.valid_until' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $validated = $this->validated();
        $validated['media_assets'] ??= [];
        $validated['approval']['submit_for_review'] = (bool) data_get($validated, 'approval.submit_for_review', false);
        $validated['approval']['is_fast_track'] = (bool) data_get($validated, 'approval.is_fast_track', false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        return $validated;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Use a short lowercase web address name such as tulong-dunong-orientation.',
            'approval.change_summary.required_if' => 'Record what changed before submitting this news/media item for review.',
        ];
    }
}

<?php

namespace App\Http\Requests\AdminConsole\PublicPages;

use App\Enums\SourceType;
use App\Models\PublicPage;
use App\Support\PublicPageProps;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class SavePublicPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('publicPage');

        if ($page instanceof PublicPage) {
            return $this->user()?->can('update', $page) ?? false;
        }

        return $this->user()?->can('create', PublicPage::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $page = $this->route('publicPage');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('public_pages', 'slug')->ignore($page instanceof PublicPage ? $page->id : null),
            ],
            'component' => ['required', 'string', 'max:255', 'starts_with:Public/'],
            'route_name' => ['required', 'string', 'max:120'],
            'page_type' => ['required', Rule::in(['landing', 'profile', 'service', 'policy', 'legal', 'contact', 'utility'])],
            'owner_label' => ['required', 'string', 'max:255'],
            'source_label' => ['required', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'verified_at' => ['required', 'date', 'before_or_equal:today'],
            'review_due_at' => ['required', 'date', 'after:today'],
            'published_at' => ['nullable', 'date'],
            'props_json' => [
                'required',
                'string',
                'max:100000',
                'json',
                function (string $attribute, mixed $value, Closure $fail): void {
                    PublicPageProps::validateJsonForSlug(
                        is_string($value) ? $value : null,
                        (string) $this->input('slug'),
                        (string) $this->input('component'),
                        $fail,
                    );
                },
            ],
            'metadata_json' => ['nullable', 'string', 'max:50000', 'json'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:500'],

            'source_record' => ['required', 'array'],
            'source_record.title' => ['required', 'string', 'max:255'],
            'source_record.url' => ['nullable', 'url', 'max:500'],
            'source_record.source_type' => ['required', new Enum(SourceType::class)],
            'source_record.source_tier' => ['required', Rule::in(['tier_1', 'tier_2', 'tier_3'])],
            'source_record.public_status' => ['required', Rule::in(['draft', 'published', 'archived', 'needs_review'])],
            'source_record.description' => ['nullable', 'string', 'max:1000'],
            'source_record.verified_at' => ['required', 'date', 'before_or_equal:today'],
            'source_record.next_review_at' => ['required', 'date', 'after:today'],

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

        $validated['props'] = PublicPageProps::decode($validated['props_json']);
        $validated['metadata'] = $this->metadataPayload($validated);
        $validated['approval']['submit_for_review'] = (bool) data_get($validated, 'approval.submit_for_review', false);
        $validated['approval']['is_fast_track'] = (bool) data_get($validated, 'approval.is_fast_track', false);

        unset($validated['props_json'], $validated['metadata_json']);

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function metadataPayload(array $validated): array
    {
        $metadata = PublicPageProps::decode($validated['metadata_json'] ?? '{}');

        $metadata['route_name'] = (string) $validated['route_name'];
        $metadata['page_type'] = (string) $validated['page_type'];
        $metadata['seo'] = array_filter([
            'title' => $validated['seo_title'] ?? null,
            'description' => $validated['seo_description'] ?? null,
            'canonical_url' => $validated['canonical_url'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ], fn (mixed $value): bool => filled($value));

        return $metadata;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Use a short lowercase web address name such as accessibility or service-directory.',
            'component.starts_with' => 'Choose a public display page from the approved Public/* list.',
            'approval.change_summary.required_if' => 'Record what changed before submitting this public page for review.',
        ];
    }
}

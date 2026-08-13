<?php

namespace App\Http\Requests\AdminConsole\Programs;

use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Enums\SourceType;
use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class SaveProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        $program = $this->route('program');

        if ($program instanceof Program) {
            return $this->user()?->can('update', $program) ?? false;
        }

        return $this->user()?->can('create', Program::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $program = $this->route('program');

        return [
            'program_category_id' => ['required', 'integer', 'exists:program_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('programs', 'slug')->ignore($program instanceof Program ? $program->id : null),
            ],
            'href' => ['required', 'string', 'max:255', 'starts_with:/'],
            'sector' => ['required', 'string', 'max:120'],
            'status' => ['required', new Enum(ProgramStatus::class)],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'responsible_office_type' => ['required', new Enum(ResponsibleOfficeType::class)],
            'responsible_office_label' => ['required', 'string', 'max:255'],
            'partner_agency' => ['nullable', 'string', 'max:255'],
            'beneficiary_group' => ['nullable', 'string', 'max:255'],
            'application_period' => ['nullable', 'string', 'max:255'],
            'benefit' => ['nullable', 'string', 'max:500'],
            'source_label' => ['required', 'string', 'max:255'],
            'source_url' => ['required', 'url', 'max:500'],
            'verified_at' => ['required', 'date', 'before_or_equal:today'],
            'review_due_at' => ['required', 'date', 'after:today'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
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

            'requirements' => ['array', 'max:30'],
            'requirements.*.group_title' => ['required', 'string', 'max:255'],
            'requirements.*.item_text' => ['required', 'string', 'max:500'],
            'requirements.*.note' => ['nullable', 'string', 'max:255'],

            'updates' => ['array', 'max:12'],
            'updates.*.title' => ['required', 'string', 'max:255'],
            'updates.*.summary' => ['required', 'string', 'max:2000'],
            'updates.*.status' => ['required', new Enum(ProgramStatus::class)],
            'updates.*.source_label' => ['nullable', 'string', 'max:255'],
            'updates.*.source_url' => ['nullable', 'url', 'max:500'],
            'updates.*.verified_at' => ['nullable', 'date', 'before_or_equal:today'],
            'updates.*.published_at' => ['nullable', 'date'],

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
        $validated['requirements'] ??= [];
        $validated['updates'] ??= [];
        $validated['approval']['submit_for_review'] = (bool) data_get($validated, 'approval.submit_for_review', false);
        $validated['approval']['is_fast_track'] = (bool) data_get($validated, 'approval.is_fast_track', false);
        $validated['is_featured'] = (bool) ($validated['is_featured'] ?? false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        return $validated;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Use a short lowercase web address name such as tulong-dunong-guidance.',
            'href.starts_with' => 'The public web address must start with /.',
            'approval.change_summary.required_if' => 'Record what changed before submitting this program for review.',
        ];
    }
}

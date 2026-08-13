<?php

namespace App\Http\Requests\AdminConsole\Projects;

use App\Enums\SourceType;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        if ($project instanceof Project) {
            return $this->user()?->can('update', $project) ?? false;
        }

        return $this->user()?->can('create', Project::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'project_category_id' => ['required', 'integer', 'exists:project_categories,id'],
            'project_status_id' => ['required', 'integer', 'exists:project_statuses,id'],
            'primary_partner_id' => ['nullable', 'integer', 'exists:project_partners,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('projects', 'slug')->ignore($project instanceof Project ? $project->id : null),
            ],
            'barangay' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'responsible_office' => ['required', 'string', 'max:255'],
            'partner_label' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:5000'],
            'evidence_summary' => ['required', 'string', 'max:5000'],
            'impact_note' => ['required', 'string', 'max:5000'],
            'image_guidance' => ['required', 'string', 'max:5000'],
            'source_label' => ['required', 'string', 'max:255'],
            'source_url' => ['required', 'url', 'max:500'],
            'verified_at' => ['required', 'date', 'before_or_equal:today'],
            'review_due_at' => ['required', 'date', 'after:today'],
            'published_at' => ['nullable', 'date'],
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

            'evidences' => ['array', 'max:20'],
            'evidences.*.title' => ['required', 'string', 'max:255'],
            'evidences.*.source_label' => ['required', 'string', 'max:255'],
            'evidences.*.source_url' => ['required', 'url', 'max:500'],
            'evidences.*.caption' => ['nullable', 'string', 'max:2000'],
            'evidences.*.alt_text' => ['nullable', 'string', 'max:2000'],
            'evidences.*.rights_note' => ['nullable', 'string', 'max:2000'],
            'evidences.*.media_path' => ['nullable', 'string', 'max:255'],
            'evidences.*.verified_at' => ['nullable', 'date', 'before_or_equal:today'],

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
        $validated['evidences'] ??= [];
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
            'slug.regex' => 'Use a short lowercase web address name such as egovph-serbisyo-hub.',
            'approval.change_summary.required_if' => 'Record what changed before submitting this project for review.',
        ];
    }
}

<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\SourceType;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectEvidence;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use App\Models\SourceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProjectFormResource
{
    public function __construct(
        private readonly ?Project $project,
        private readonly string $mode,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $project = $this->project;
        $source = $project?->sourceRecords->sortByDesc(
            fn (SourceRecord $record): int => $record->verified_at?->getTimestamp() ?? 0,
        )->first();

        return [
            'mode' => $this->mode,
            'title' => $project instanceof Project ? 'Edit '.$project->title : 'Create project record',
            'action' => $project instanceof Project
                ? route('admin-console.projects.update', $project)
                : route('admin-console.projects.store'),
            'method' => $project instanceof Project ? 'put' : 'post',
            'backHref' => $project instanceof Project
                ? route('admin-console.projects.show', $project)
                : route('admin-console.projects.index'),
            'indexHref' => route('admin-console.projects.index'),
            'projectHref' => $project instanceof Project ? route('admin-console.projects.show', $project) : null,
            'values' => $this->values($project, $source),
            'options' => $this->options(),
            'governanceNote' => 'Project records must identify the responsible office, partner attribution, official source, evidence basis, and media-rights status before governed publication.',
            'mediaUploadFallbackNote' => 'This custom editor manages evidence metadata. New media file uploads remain in the restricted fallback until custom upload parity is implemented.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function values(?Project $project, ?SourceRecord $source): array
    {
        return [
            'project_category_id' => $project?->project_category_id ? (string) $project->project_category_id : '',
            'project_status_id' => $project?->project_status_id ? (string) $project->project_status_id : '',
            'primary_partner_id' => $project?->primary_partner_id ? (string) $project->primary_partner_id : '',
            'title' => $project?->title ?? '',
            'slug' => $project?->slug ?? '',
            'barangay' => $project?->barangay ?? '',
            'location' => $project?->location ?? '',
            'responsible_office' => $project?->responsible_office ?? '',
            'partner_label' => $project?->partner_label ?? '',
            'summary' => $project?->summary ?? '',
            'evidence_summary' => $project?->evidence_summary ?? '',
            'impact_note' => $project?->impact_note ?? '',
            'image_guidance' => $project?->image_guidance ?? '',
            'source_label' => $project?->source_label ?? $source?->title ?? '',
            'source_url' => $project?->source_url ?? $source?->url ?? '',
            'verified_at' => $this->dateValue($project?->verified_at ?? $source?->verified_at),
            'review_due_at' => $this->dateValue($project?->review_due_at ?? $source?->next_review_at),
            'published_at' => $this->dateTimeValue($project?->published_at),
            'sort_order' => (int) ($project?->sort_order ?? 0),
            'source_record' => [
                'title' => $source?->title ?? $project?->source_label ?? '',
                'url' => $source?->url ?? $project?->source_url ?? '',
                'source_type' => $source?->source_type->value ?? SourceType::PrimaryOfficial->value,
                'source_tier' => $source?->source_tier ?? 'tier_1',
                'public_status' => $source?->public_status ?? 'published',
                'description' => $source?->description ?? '',
                'verified_at' => $this->dateValue($source?->verified_at ?? $project?->verified_at),
                'next_review_at' => $this->dateValue($source?->next_review_at ?? $project?->review_due_at),
            ],
            'evidences' => $this->evidences($project),
            'approval' => [
                'submit_for_review' => false,
                'change_summary' => '',
                'is_fast_track' => false,
                'valid_until' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'categories' => ProjectCategory::query()
                ->active()
                ->ordered()
                ->get(['id', 'name'])
                ->map(fn (ProjectCategory $category): array => [
                    'value' => (string) $category->id,
                    'label' => $category->name,
                ])
                ->values()
                ->all(),
            'statuses' => ProjectStatus::query()
                ->active()
                ->ordered()
                ->get(['id', 'label', 'value'])
                ->map(fn (ProjectStatus $status): array => [
                    'value' => (string) $status->id,
                    'label' => $status->label,
                    'description' => $status->value->label(),
                ])
                ->values()
                ->all(),
            'partners' => ProjectPartner::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'office_type'])
                ->map(fn (ProjectPartner $partner): array => [
                    'value' => (string) $partner->id,
                    'label' => $partner->name,
                    'description' => str_replace('_', ' ', $partner->office_type),
                ])
                ->values()
                ->all(),
            'sourceTypes' => collect(SourceType::cases())
                ->map(fn (SourceType $type): array => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ])
                ->values()
                ->all(),
            'sourceTiers' => [
                ['value' => 'tier_1', 'label' => 'Tier 1 - official source'],
                ['value' => 'tier_2', 'label' => 'Tier 2 - verified partner source'],
                ['value' => 'tier_3', 'label' => 'Tier 3 - historical reference'],
            ],
            'sourcePublicStatuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'published', 'label' => 'Published'],
                ['value' => 'archived', 'label' => 'Archived'],
                ['value' => 'needs_review', 'label' => 'Needs review'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function evidences(?Project $project): array
    {
        if (! $project instanceof Project || ! $project->relationLoaded('evidences')) {
            return [[
                'title' => '',
                'source_label' => '',
                'source_url' => '',
                'caption' => '',
                'alt_text' => '',
                'rights_note' => '',
                'media_path' => '',
                'verified_at' => '',
            ]];
        }

        return $project->evidences
            ->map(fn (ProjectEvidence $evidence): array => [
                'title' => $evidence->title,
                'source_label' => $evidence->source_label,
                'source_url' => $evidence->source_url,
                'caption' => $evidence->caption ?? '',
                'alt_text' => $evidence->alt_text ?? '',
                'rights_note' => $evidence->rights_note ?? '',
                'media_path' => $evidence->media_path ?? '',
                'verified_at' => $this->dateValue($evidence->verified_at) ?? '',
            ])
            ->values()
            ->all() ?: [[
                'title' => '',
                'source_label' => '',
                'source_url' => '',
                'caption' => '',
                'alt_text' => '',
                'rights_note' => '',
                'media_path' => '',
                'verified_at' => '',
            ]];
    }

    private function dateValue(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->toDateString()
            : null;
    }

    private function dateTimeValue(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('Y-m-d\TH:i')
            : null;
    }
}

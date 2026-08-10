<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Enums\SourceType;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramRequirement;
use App\Models\ProgramUpdate;
use App\Models\SourceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProgramFormResource
{
    public function __construct(
        private readonly ?Program $program,
        private readonly string $mode,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $program = $this->program;
        $source = $program?->sourceRecords->sortByDesc(
            fn (SourceRecord $record): int => $record->verified_at?->getTimestamp() ?? 0,
        )->first();

        return [
            'mode' => $this->mode,
            'title' => $program instanceof Program ? 'Edit '.$program->title : 'Create program record',
            'action' => $program instanceof Program
                ? route('admin-console.programs.update', $program)
                : route('admin-console.programs.store'),
            'method' => $program instanceof Program ? 'put' : 'post',
            'backHref' => $program instanceof Program
                ? route('admin-console.programs.show', $program)
                : route('admin-console.programs.index'),
            'indexHref' => route('admin-console.programs.index'),
            'programHref' => $program instanceof Program ? route('admin-console.programs.show', $program) : null,
            'values' => $this->values($program, $source),
            'options' => $this->options(),
            'governanceNote' => 'Program edits remain draft-owned until submitted to the governed approval queue. Official source, verification, and review dates are required before submission.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function values(?Program $program, ?SourceRecord $source): array
    {
        return [
            'program_category_id' => $program?->program_category_id ? (string) $program->program_category_id : '',
            'title' => $program?->title ?? '',
            'slug' => $program?->slug ?? '',
            'href' => $program?->href ?? '/programs',
            'sector' => $program?->sector ?? '',
            'status' => $program?->status->value ?? ProgramStatus::ForAnnouncement->value,
            'summary' => $program?->summary ?? '',
            'description' => $program?->description ?? '',
            'responsible_office_type' => $program?->responsible_office_type->value ?? ResponsibleOfficeType::DistrictOffice->value,
            'responsible_office_label' => $program?->responsible_office_label ?? 'District Office',
            'partner_agency' => $program?->partner_agency ?? '',
            'beneficiary_group' => $program?->beneficiary_group ?? '',
            'application_period' => $program?->application_period ?? '',
            'benefit' => $program?->benefit ?? '',
            'source_label' => $program?->source_label ?? $source?->title ?? '',
            'source_url' => $program?->source_url ?? $source?->url ?? '',
            'verified_at' => $this->dateValue($program?->verified_at ?? $source?->verified_at),
            'review_due_at' => $this->dateValue($program?->review_due_at ?? $source?->next_review_at),
            'published_at' => $this->dateTimeValue($program?->published_at),
            'is_featured' => (bool) ($program?->is_featured ?? false),
            'sort_order' => (int) ($program?->sort_order ?? 0),
            'source_record' => [
                'title' => $source?->title ?? $program?->source_label ?? '',
                'url' => $source?->url ?? $program?->source_url ?? '',
                'source_type' => $source?->source_type->value ?? SourceType::PrimaryOfficial->value,
                'source_tier' => $source?->source_tier ?? 'tier_1',
                'public_status' => $source?->public_status ?? 'published',
                'description' => $source?->description ?? '',
                'verified_at' => $this->dateValue($source?->verified_at ?? $program?->verified_at),
                'next_review_at' => $this->dateValue($source?->next_review_at ?? $program?->review_due_at),
            ],
            'requirements' => $this->requirements($program),
            'updates' => $this->updates($program),
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
            'categories' => ProgramCategory::query()
                ->active()
                ->ordered()
                ->get(['id', 'name'])
                ->map(fn (ProgramCategory $category): array => [
                    'value' => (string) $category->id,
                    'label' => $category->name,
                ])
                ->values()
                ->all(),
            'statuses' => $this->assocOptions(ProgramStatus::options()),
            'responsibleOfficeTypes' => $this->assocOptions(ResponsibleOfficeType::options()),
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
     * @return list<array{group_title: string, item_text: string, note: string}>
     */
    private function requirements(?Program $program): array
    {
        if (! $program instanceof Program || ! $program->relationLoaded('requirements')) {
            return [['group_title' => 'General requirements', 'item_text' => '', 'note' => '']];
        }

        return $program->requirements
            ->map(fn (ProgramRequirement $requirement): array => [
                'group_title' => $requirement->group_title,
                'item_text' => $requirement->item_text,
                'note' => $requirement->note ?? '',
            ])
            ->values()
            ->all() ?: [['group_title' => 'General requirements', 'item_text' => '', 'note' => '']];
    }

    /**
     * @return list<array<string, string>>
     */
    private function updates(?Program $program): array
    {
        if (! $program instanceof Program || ! $program->relationLoaded('updates')) {
            return [];
        }

        return $program->updates
            ->sortByDesc('published_at')
            ->map(fn (ProgramUpdate $update): array => [
                'title' => $update->title,
                'summary' => $update->summary,
                'status' => $update->status->value,
                'source_label' => $update->source_label ?? '',
                'source_url' => $update->source_url ?? '',
                'verified_at' => $this->dateValue($update->verified_at) ?? '',
                'published_at' => $this->dateTimeValue($update->published_at) ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $options
     * @return list<array{value: string, label: string}>
     */
    private function assocOptions(array $options): array
    {
        return collect($options)
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
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

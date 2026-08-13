<?php

namespace App\Services;

use App\Enums\LegislativeRoleValue;
use App\Enums\LegislativeStatusTone;
use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Models\Inquiry;
use App\Models\InquiryCategory;
use App\Models\LegislativeRole;
use App\Models\LegislativeStatus;
use App\Models\LegislativeTopic;
use App\Models\NewsCategory;
use App\Models\NewsStatus;
use App\Models\ProgramCategory;
use App\Models\ProjectCategory;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminReferenceDataRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            'program-categories' => [
                'label' => 'Program categories',
                'singular' => 'Program category',
                'model' => ProgramCategory::class,
                'title' => 'name',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'description', 'sort_order', 'is_active'],
                'usage' => ['programs' => 'Programs'],
                'description' => 'Controls public program grouping and service-directory filtering.',
            ],
            'project-categories' => [
                'label' => 'Project categories',
                'singular' => 'Project category',
                'model' => ProjectCategory::class,
                'title' => 'name',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'description', 'sort_order', 'is_active'],
                'usage' => ['projects' => 'Projects'],
                'description' => 'Controls project/result category filters and public labels.',
            ],
            'project-statuses' => [
                'label' => 'Project statuses',
                'singular' => 'Project status',
                'model' => ProjectStatus::class,
                'title' => 'label',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'value', 'label', 'tone', 'sort_order', 'is_active'],
                'usage' => ['projects' => 'Projects'],
                'description' => 'Controls project status values, tones, and active project filters.',
                'options' => [
                    'value' => $this->enumOptions(ProjectStatusValue::cases()),
                    'tone' => $this->enumOptions(ProjectStatusTone::cases()),
                ],
            ],
            'project-partners' => [
                'label' => 'Project partners',
                'singular' => 'Project partner',
                'model' => ProjectPartner::class,
                'title' => 'name',
                'subtitle' => 'office_type',
                'fields' => ['name', 'slug', 'office_type', 'website_url', 'notes', 'is_active'],
                'usage' => ['projects' => 'Projects'],
                'description' => 'Controls partner attribution for public project records.',
            ],
            'news-categories' => [
                'label' => 'News categories',
                'singular' => 'News category',
                'model' => NewsCategory::class,
                'title' => 'name',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'description', 'sort_order', 'is_active'],
                'usage' => ['newsRecords' => 'News records'],
                'description' => 'Controls official-update category filters.',
            ],
            'news-statuses' => [
                'label' => 'News statuses',
                'singular' => 'News status',
                'model' => NewsStatus::class,
                'title' => 'label',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'value', 'label', 'tone', 'sort_order', 'is_active'],
                'usage' => ['newsRecords' => 'News records'],
                'description' => 'Controls current, archived, and stale-review news states.',
                'options' => [
                    'value' => $this->enumOptions(NewsStatusValue::cases()),
                    'tone' => $this->enumOptions(NewsStatusTone::cases()),
                ],
            ],
            'legislative-roles' => [
                'label' => 'Legislative roles',
                'singular' => 'Legislative role',
                'model' => LegislativeRole::class,
                'title' => 'label',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'value', 'label', 'sort_order', 'is_active'],
                'usage' => ['records' => 'Legislative records'],
                'description' => 'Controls principal-author, co-author, and sponsor role filters.',
                'options' => [
                    'value' => $this->enumOptions(LegislativeRoleValue::cases()),
                ],
            ],
            'legislative-statuses' => [
                'label' => 'Legislative statuses',
                'singular' => 'Legislative status',
                'model' => LegislativeStatus::class,
                'title' => 'label',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'label', 'tone', 'sort_order', 'is_active'],
                'usage' => ['records' => 'Legislative records'],
                'description' => 'Controls legislative progress labels and tone badges.',
                'options' => [
                    'tone' => $this->enumOptions(LegislativeStatusTone::cases()),
                ],
            ],
            'legislative-topics' => [
                'label' => 'Legislative topics',
                'singular' => 'Legislative topic',
                'model' => LegislativeTopic::class,
                'title' => 'name',
                'subtitle' => 'slug',
                'fields' => ['name', 'slug', 'description', 'sort_order', 'is_active'],
                'usage' => ['records' => 'Legislative records'],
                'description' => 'Controls legislative topic filters and content grouping.',
            ],
            'inquiry-categories' => [
                'label' => 'Inquiry categories',
                'singular' => 'Inquiry category',
                'model' => InquiryCategory::class,
                'title' => 'label',
                'subtitle' => 'code',
                'fields' => ['code', 'label', 'description', 'sort_order', 'is_active'],
                'description' => 'Controls public inquiry form categories and staff routing labels.',
                'usageCallback' => fn (InquiryCategory $category): int => Inquiry::query()
                    ->where('inquiry_category', $category->code)
                    ->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $type): array
    {
        $config = $this->all()[$type] ?? null;

        abort_if($config === null, 404);

        return ['type' => $type, ...$config];
    }

    /**
     * @return list<array{type: string, label: string, description: string, indexHref: string}>
     */
    public function navigationOptions(): array
    {
        return collect($this->all())
            ->map(fn (array $config, string $type): array => [
                'type' => $type,
                'label' => (string) $config['label'],
                'description' => (string) $config['description'],
                'indexHref' => route('admin-console.reference-data.index', ['type' => $type]),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<\BackedEnum>  $cases
     * @return list<array{value: string, label: string}>
     */
    private function enumOptions(array $cases): array
    {
        return collect($cases)
            ->map(fn (\BackedEnum $case): array => [
                'value' => (string) $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : Str::headline((string) $case->value),
            ])
            ->values()
            ->all();
    }

    public function titleFor(Model $record, array $config): string
    {
        return (string) ($record->getAttribute((string) $config['title']) ?? class_basename($record).' #'.$record->getKey());
    }

    public function subtitleFor(Model $record, array $config): ?string
    {
        $subtitle = $record->getAttribute((string) $config['subtitle']);

        return is_scalar($subtitle) ? (string) $subtitle : null;
    }
}

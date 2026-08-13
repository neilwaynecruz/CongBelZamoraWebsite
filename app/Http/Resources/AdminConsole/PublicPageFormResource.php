<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\SourceType;
use App\Models\PublicPage;
use App\Models\SourceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicPageFormResource
{
    public function __construct(
        private readonly ?PublicPage $page,
        private readonly string $mode,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $page = $this->page;
        $source = $page?->sourceRecords->sortByDesc(
            fn (SourceRecord $sourceRecord): int => $sourceRecord->verified_at?->getTimestamp() ?? 0,
        )->first();

        return [
            'mode' => $this->mode,
            'title' => $page instanceof PublicPage ? 'Edit '.$page->title : 'Create public page',
            'action' => $page instanceof PublicPage
                ? route('admin-console.public-pages.update', $page)
                : route('admin-console.public-pages.store'),
            'method' => $page instanceof PublicPage ? 'put' : 'post',
            'backHref' => $page instanceof PublicPage
                ? route('admin-console.public-pages.show', $page)
                : route('admin-console.public-pages.index'),
            'indexHref' => route('admin-console.public-pages.index'),
            'recordHref' => $page instanceof PublicPage ? route('admin-console.public-pages.show', $page) : null,
            'values' => $this->values($page, $source),
            'options' => $this->options(),
            'governanceNote' => 'Public pages must keep their content settings valid, name the source and page owner, and include search details before review.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function values(?PublicPage $page, ?SourceRecord $source): array
    {
        $metadata = $page?->metadata ?? [];

        return [
            'title' => $page?->title ?? '',
            'slug' => $page?->slug ?? '',
            'component' => $page?->component ?? 'Public/PageShell',
            'route_name' => (string) data_get($metadata, 'route_name', $this->routeNameForSlug($page?->slug)),
            'page_type' => (string) data_get($metadata, 'page_type', $this->pageTypeForSlug($page?->slug)),
            'owner_label' => $page?->owner_label ?? '',
            'source_label' => $page?->source_label ?? $source?->title ?? '',
            'source_url' => $page?->source_url ?? $source?->url ?? '',
            'verified_at' => $this->dateValue($page?->verified_at ?? $source?->verified_at),
            'review_due_at' => $this->dateValue($page?->review_due_at ?? $source?->next_review_at),
            'published_at' => $this->dateTimeValue($page?->published_at),
            'seo_title' => (string) data_get($metadata, 'seo.title', ''),
            'seo_description' => (string) data_get($metadata, 'seo.description', ''),
            'canonical_url' => (string) data_get($metadata, 'seo.canonical_url', ''),
            'og_image' => (string) data_get($metadata, 'seo.og_image', ''),
            'props_json' => $this->json($page?->props ?? ['breadcrumbs' => []]),
            'metadata_json' => $this->json($this->metadataForEditor($metadata)),
            'source_record' => [
                'title' => $source?->title ?? $page?->source_label ?? '',
                'url' => $source?->url ?? $page?->source_url ?? '',
                'source_type' => $source?->source_type->value ?? SourceType::PrimaryOfficial->value,
                'source_tier' => $source?->source_tier ?? 'tier_1',
                'public_status' => $source?->public_status ?? 'published',
                'description' => $source?->description ?? '',
                'verified_at' => $this->dateValue($source?->verified_at ?? $page?->verified_at),
                'next_review_at' => $this->dateValue($source?->next_review_at ?? $page?->review_due_at),
            ],
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
            'pageTypes' => [
                ['value' => 'landing', 'label' => 'Landing page'],
                ['value' => 'profile', 'label' => 'Profile / biography'],
                ['value' => 'service', 'label' => 'Service information'],
                ['value' => 'policy', 'label' => 'Policy page'],
                ['value' => 'legal', 'label' => 'Legal notice'],
                ['value' => 'contact', 'label' => 'Contact page'],
                ['value' => 'utility', 'label' => 'Utility page'],
            ],
            'components' => [
                ['value' => 'Public/Home', 'label' => 'Home'],
                ['value' => 'Public/About', 'label' => 'About'],
                ['value' => 'Public/Contact', 'label' => 'Contact'],
                ['value' => 'Public/PolicyPage', 'label' => 'Policy page'],
                ['value' => 'Public/PageShell', 'label' => 'Generic page shell'],
            ],
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
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function metadataForEditor(array $metadata): array
    {
        unset($metadata['route_name'], $metadata['page_type'], $metadata['seo']);

        return $metadata;
    }

    private function routeNameForSlug(?string $slug): string
    {
        return match ($slug) {
            'home' => 'home',
            'about' => 'public.about',
            'contact' => 'public.contact',
            'privacy' => 'public.privacy',
            'accessibility' => 'public.accessibility',
            'disclaimer' => 'public.disclaimer',
            default => '',
        };
    }

    private function pageTypeForSlug(?string $slug): string
    {
        return match ($slug) {
            'home' => 'landing',
            'about' => 'profile',
            'contact' => 'contact',
            'privacy', 'accessibility' => 'policy',
            'disclaimer' => 'legal',
            default => 'utility',
        };
    }

    private function dateValue(mixed $value): string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->toDateString()
            : '';
    }

    private function dateTimeValue(mixed $value): string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('Y-m-d\TH:i')
            : '';
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function json(array $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }
}

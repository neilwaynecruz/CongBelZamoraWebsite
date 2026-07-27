<?php

namespace App\Services;

use App\Models\PublicPage;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class PublicStaticPageReadService
{
    public function __construct(private readonly PublicCacheInvalidator $cacheInvalidator) {}

    /**
     * @return array<string, mixed>
     */
    public function props(string $slug): array
    {
        /** @var array<string, mixed> $props */
        $props = Cache::remember(
            $this->cacheInvalidator->staticPageKey($slug),
            (int) config('belzamora.cache.public_ttl_seconds', 60),
            function () use ($slug): array {
                $page = $this->page($slug);

                return $this->withReviewMetadata($page, $page->props);
            },
        );

        return $props;
    }

    /**
     * @return array<string, mixed>
     */
    public function contactProps(Request $request): array
    {
        $props = $this->props('contact');

        return [
            ...$props,
            'inquiryAction' => route('public.contact.inquiries.store'),
            'confirmation' => $request->session()->get('inquiryConfirmation'),
        ];
    }

    public function component(string $slug): string
    {
        /** @var string $component */
        $component = Cache::remember(
            $this->cacheInvalidator->staticPageComponentKey($slug),
            (int) config('belzamora.cache.public_ttl_seconds', 60),
            fn (): string => $this->page($slug)->component,
        );

        return $component;
    }

    private function page(string $slug): PublicPage
    {
        return PublicPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function withReviewMetadata(PublicPage $page, array $props): array
    {
        if (! Arr::has($props, 'verifiedAt')) {
            $props['verifiedAt'] = $this->formatManilaDate($page->verified_at)
                ?? 'Source review pending';
        }

        $props['contentReview'] = [
            'owner' => $page->owner_label,
            'sourceLabel' => $page->source_label,
            'sourceUrl' => $page->source_url,
            'verifiedAt' => $this->formatManilaDate($page->verified_at),
            'reviewDueAt' => $this->formatManilaDate($page->review_due_at),
        ];

        return $props;
    }

    private function formatManilaDate(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)
                ->timezone('Asia/Manila')
                ->format('F j, Y');
        }

        if (is_string($value) && $value !== '') {
            return CarbonImmutable::parse($value)
                ->timezone('Asia/Manila')
                ->format('F j, Y');
        }

        return null;
    }
}

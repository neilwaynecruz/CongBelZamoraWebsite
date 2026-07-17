<?php

namespace App\Services;

use App\Models\PublicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PublicStaticPageReadService
{
    /**
     * @return array<string, mixed>
     */
    public function props(string $slug): array
    {
        $page = $this->page($slug);

        return $this->withReviewMetadata($page, $page->props);
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
        return $this->page($slug)->component;
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
            $props['verifiedAt'] = $page->verified_at?->timezone('Asia/Manila')->format('F j, Y')
                ?? 'Source review pending';
        }

        $props['contentReview'] = [
            'owner' => $page->owner_label,
            'sourceLabel' => $page->source_label,
            'sourceUrl' => $page->source_url,
            'verifiedAt' => $page->verified_at?->timezone('Asia/Manila')->format('F j, Y'),
            'reviewDueAt' => $page->review_due_at?->timezone('Asia/Manila')->format('F j, Y'),
        ];

        return $props;
    }
}

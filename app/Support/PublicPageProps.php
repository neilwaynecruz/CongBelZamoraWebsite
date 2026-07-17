<?php

namespace App\Support;

use Closure;

class PublicPageProps
{
    /**
     * @return array<string, mixed>
     */
    public static function decode(?string $json): array
    {
        $decoded = json_decode($json ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function validateJsonForSlug(
        ?string $json,
        string $slug,
        string $component,
        Closure $fail,
    ): void {
        $decoded = json_decode($json ?: '', true);

        if (! is_array($decoded)) {
            $fail('The Inertia props must be a valid JSON object.');

            return;
        }

        if (! str_starts_with($component, 'Public/')) {
            $fail('The component must point to a public Inertia component.');
        }

        foreach (self::requiredKeys($slug) as $key) {
            if (! array_key_exists($key, $decoded)) {
                $fail("The {$slug} page props must include the [{$key}] key.");
            }
        }
    }

    /**
     * @return array<int, string>
     */
    public static function requiredSlugs(): array
    {
        return ['home', 'about', 'contact', 'privacy', 'accessibility', 'disclaimer'];
    }

    /**
     * @return array<int, string>
     */
    private static function requiredKeys(string $slug): array
    {
        return match ($slug) {
            'home' => ['breadcrumbs', 'quickActions', 'featuredPrograms', 'legislativeSpotlight', 'latestNews'],
            'about' => ['breadcrumbs', 'profile', 'facts', 'timeline', 'committeeMemberships', 'valuePillars', 'responsibilityBoundaries', 'sourceLinks', 'verifiedAt'],
            'contact' => ['breadcrumbs', 'office', 'channels', 'privacyHighlights', 'responseExpectations', 'verifiedAt'],
            'privacy' => ['breadcrumbs', 'effectiveDate', 'sections', 'privacyContact'],
            'accessibility' => ['breadcrumbs', 'reviewDate', 'sections', 'supportContact'],
            'disclaimer' => ['breadcrumbs', 'reviewDate', 'sections', 'contact'],
            default => ['breadcrumbs'],
        };
    }
}

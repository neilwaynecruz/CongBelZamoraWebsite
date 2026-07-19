<?php

namespace Tests\Feature;

use Database\Seeders\LegislativeRecordSeeder;
use Database\Seeders\NewsRecordSeeder;
use Database\Seeders\ProgramAndServiceGuideSeeder;
use Database\Seeders\ProjectRecordSeeder;
use Database\Seeders\PublicPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase9QualityAssuranceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PublicPageSeeder::class);
        $this->seed(ProgramAndServiceGuideSeeder::class);
        $this->seed(LegislativeRecordSeeder::class);
        $this->seed(ProjectRecordSeeder::class);
        $this->seed(NewsRecordSeeder::class);
    }

    public function test_sitemap_public_routes_resolve_successfully(): void
    {
        $sitemap = simplexml_load_file(public_path('sitemap.xml'));

        $this->assertNotFalse($sitemap);

        foreach ($sitemap->url as $url) {
            $path = parse_url((string) $url->loc, PHP_URL_PATH) ?: '/';

            $this->get($path)->assertOk();
        }
    }

    public function test_public_responses_include_baseline_security_headers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_public_inquiry_submission_is_rate_limited(): void
    {
        $payload = [
            'citizen_name' => 'Rate Limit Tester',
            'contact_preference' => 'email',
            'citizen_email' => 'qa@example.com',
            'citizen_mobile' => null,
            'inquiry_category' => 'general_inquiry',
            'message_body' => 'Phase 9 rate-limit validation message for the public inquiry form.',
            'privacy_consent' => '1',
        ];

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->from(route('public.contact'))
                ->post(route('public.contact.inquiries.store'), $payload)
                ->assertRedirect(route('public.contact'));
        }

        $this->from(route('public.contact'))
            ->post(route('public.contact.inquiries.store'), $payload)
            ->assertTooManyRequests();
    }
}

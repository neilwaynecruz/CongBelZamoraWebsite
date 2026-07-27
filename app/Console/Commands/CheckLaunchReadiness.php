<?php

namespace App\Console\Commands;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\PublicPage;
use App\Support\PublicPageProps;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckLaunchReadiness extends Command
{
    protected $signature = 'launch:readiness
        {--production : Include production environment checks}
        {--smoke-url= : Optional base URL for public HTTP smoke checks}
        {--allow-pending-approval : Report pending human approvals without failing the command}';

    protected $description = 'Check human approvals, public pages, environment settings, and optional smoke URLs before launch.';

    public function handle(): int
    {
        $failed = false;

        $failed = $this->checkHumanApprovals() || $failed;
        $failed = $this->checkRequiredPublicPages() || $failed;

        if ($this->option('production')) {
            $failed = $this->checkProductionEnvironment() || $failed;
        }

        $smokeUrl = $this->option('smoke-url');

        if (is_string($smokeUrl) && $smokeUrl !== '') {
            $failed = $this->checkSmokeUrls($smokeUrl) || $failed;
        }

        if ($failed) {
            $this->error('Launch readiness checks found items that must be resolved.');

            return self::FAILURE;
        }

        $this->info('Launch readiness checks passed.');

        return self::SUCCESS;
    }

    private function checkHumanApprovals(): bool
    {
        $approvals = ContentApproval::query()
            ->with('approvable')
            ->where('metadata->launch_gate', true)
            ->orderBy('submitted_at')
            ->get();

        if ($approvals->isEmpty()) {
            $this->error('No launch approval checklist records found. Run LaunchApprovalChecklistSeeder.');

            return true;
        }

        $pending = $approvals->reject(
            fn (ContentApproval $approval): bool => in_array($approval->status, [
                ContentApprovalStatus::Approved,
                ContentApprovalStatus::Published,
            ], true),
        );

        if ($pending->isEmpty()) {
            $this->info('Human content approvals: all launch checklist items are approved.');

            return false;
        }

        $this->warn('Human content approvals still pending:');

        foreach ($pending as $approval) {
            $this->line('- '.(string) data_get($approval->metadata, 'launch_item', 'Launch approval #'.$approval->id).' ['.$approval->status->label().']');
        }

        return ! $this->option('allow-pending-approval');
    }

    private function checkRequiredPublicPages(): bool
    {
        $publishedSlugs = PublicPage::query()
            ->published()
            ->whereIn('slug', PublicPageProps::requiredSlugs())
            ->pluck('slug')
            ->all();

        $missing = array_values(array_diff(PublicPageProps::requiredSlugs(), $publishedSlugs));

        if ($missing !== []) {
            $this->error('Missing required public pages: '.implode(', ', $missing));

            return true;
        }

        $this->info('Required public pages: published.');

        return false;
    }

    private function checkProductionEnvironment(): bool
    {
        $failed = false;
        $checks = [
            'APP_ENV must be production' => app()->environment('production'),
            'APP_DEBUG must be false' => config('app.debug') === false,
            'APP_KEY must be set' => filled(config('app.key')),
            'APP_URL must use HTTPS' => Str::startsWith((string) config('app.url'), 'https://'),
            'MAIL_MAILER must not be log or array' => ! in_array((string) config('mail.default'), ['log', 'array'], true),
            'QUEUE_CONNECTION must not be sync' => (string) config('queue.default') !== 'sync',
            'CACHE_STORE must not be array' => (string) config('cache.default') !== 'array',
            'INQUIRY_STAFF_NOTIFICATION_EMAIL should be configured' => is_string(config('belzamora.inquiries.staff_notification_email'))
                && config('belzamora.inquiries.staff_notification_email') !== '',
        ];

        foreach ($checks as $label => $passes) {
            if ($passes) {
                $this->info($label.': ok');

                continue;
            }

            $this->error($label.': failed');
            $failed = true;
        }

        return $failed;
    }

    private function checkSmokeUrls(string $baseUrl): bool
    {
        $failed = false;
        $baseUrl = rtrim($baseUrl, '/');
        $paths = [
            '/',
            '/programs',
            '/programs/education/tulong-dunong',
            '/contact',
            '/projects',
            '/news',
            '/sitemap.xml',
            '/robots.txt',
            '/assets/images/brand/social-preview.png',
        ];

        foreach ($paths as $path) {
            $url = $baseUrl.$path;

            try {
                $response = Http::timeout(10)->get($url);
            } catch (\Throwable $exception) {
                $this->error("Smoke {$url}: ".$exception->getMessage());
                $failed = true;

                continue;
            }

            if ($response->successful()) {
                $this->info("Smoke {$url}: {$response->status()}");

                continue;
            }

            $this->error("Smoke {$url}: {$response->status()}");
            $failed = true;
        }

        return $failed;
    }
}

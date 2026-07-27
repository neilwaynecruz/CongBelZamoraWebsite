<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PublicCacheInvalidator
{
    public const ADMIN_DASHBOARD_COUNTS = 'admin_dashboard_counts';

    public const ADMIN_REPORTS_INQUIRY_VOLUME = 'admin_reports_inquiry_volume';

    public const ADMIN_REPORTS_PUBLISH_LATENCY = 'admin_reports_publish_latency';

    public const ADMIN_REPORTS_STALE_BREAKDOWN = 'admin_reports_stale_breakdown';

    public const ADMIN_REPORTS_PROJECT_BARANGAY = 'admin_reports_project_barangay';

    public const PROGRAMS_INDEX = 'public.programs.index';

    public const PROGRAMS_GABAY = 'public.programs.gabay';

    public const PROGRAMS_TULONG_DUNONG = 'public.programs.tulong-dunong';

    public const LEGISLATION_INDEX = 'public.legislation.index';

    public const PROJECTS_INDEX = 'public.projects.index';

    public const NEWS_INDEX = 'public.news.index';

    public function forgetAdminDashboardCounts(): void
    {
        Cache::forget(self::ADMIN_DASHBOARD_COUNTS);
        $this->forgetAdminReports();
    }

    public function forgetAdminReports(): void
    {
        Cache::forget(self::ADMIN_REPORTS_PUBLISH_LATENCY);
        Cache::forget(self::ADMIN_REPORTS_STALE_BREAKDOWN);
        Cache::forget(self::ADMIN_REPORTS_PROJECT_BARANGAY);

        foreach ([7, 30, 90] as $days) {
            Cache::forget(self::ADMIN_REPORTS_INQUIRY_VOLUME.':'.$days);
        }
    }

    public function forgetStaticPage(string $slug): void
    {
        Cache::forget($this->staticPageKey($slug));
        Cache::forget($this->staticPageComponentKey($slug));
    }

    public function forgetPrograms(): void
    {
        Cache::forget(self::PROGRAMS_INDEX);
        Cache::forget(self::PROGRAMS_GABAY);
        Cache::forget(self::PROGRAMS_TULONG_DUNONG);
    }

    public function forgetLegislation(): void
    {
        Cache::forget(self::LEGISLATION_INDEX);
    }

    public function forgetProjects(): void
    {
        Cache::forget(self::PROJECTS_INDEX);
    }

    public function forgetNews(): void
    {
        Cache::forget(self::NEWS_INDEX);
    }

    public function forgetAllPublic(): void
    {
        foreach (['home', 'about', 'contact', 'privacy', 'accessibility', 'disclaimer'] as $slug) {
            $this->forgetStaticPage($slug);
        }

        $this->forgetPrograms();
        $this->forgetLegislation();
        $this->forgetProjects();
        $this->forgetNews();
        $this->forgetAdminDashboardCounts();
    }

    public function forgetForModel(?Model $model): void
    {
        if ($model === null) {
            $this->forgetAllPublic();

            return;
        }

        $class = $model::class;

        match (true) {
            str_contains($class, 'PublicPage') => $this->forgetStaticPage((string) $model->getAttribute('slug')),
            str_contains($class, 'Program'),
            str_contains($class, 'ServiceGuide'),
            str_contains($class, 'ProgramUpdate'),
            str_contains($class, 'ProgramRequirement') => $this->forgetPrograms(),
            str_contains($class, 'Legislative') => $this->forgetLegislation(),
            str_contains($class, 'Project') => $this->forgetProjects(),
            str_contains($class, 'News') => $this->forgetNews(),
            default => $this->forgetAllPublic(),
        };

        $this->forgetAdminDashboardCounts();
    }

    public function staticPageKey(string $slug): string
    {
        return 'public.page.'.$slug;
    }

    public function staticPageComponentKey(string $slug): string
    {
        return 'public.page.'.$slug.'.component';
    }
}

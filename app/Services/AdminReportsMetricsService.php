<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryCategory;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\ServiceGuide;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminReportsMetricsService
{
    public function __construct(private readonly AdminDashboardMetricsService $dashboardMetrics) {}

    public function canViewInquiryReports(User $user): bool
    {
        return $user->canManageInquiries() || $user->canAuditRecords();
    }

    public function canViewPublishReports(User $user): bool
    {
        return $user->canReviewPublicContent() || $user->canAuditRecords();
    }

    public function canViewStaleReports(User $user): bool
    {
        return $this->dashboardMetrics->canViewContentHealth($user);
    }

    public function canViewProjectReports(User $user): bool
    {
        return $user->canManageProjects()
            || $user->canReviewPublicContent()
            || $user->canAuditRecords();
    }

    /**
     * @return list<array{category: string, label: string, total: int}>
     */
    public function getInquiryVolumeByCategory(User $user, int $days = 30): array
    {
        if (! $this->canViewInquiryReports($user)) {
            return [];
        }

        $ttl = (int) config('belzamora.reports.cache_ttl_seconds', 60);

        /** @var list<array{category: string, label: string, total: int}> $rows */
        $rows = Cache::remember(
            PublicCacheInvalidator::ADMIN_REPORTS_INQUIRY_VOLUME.':'.$days,
            $ttl,
            function () use ($days): array {
                $counts = Inquiry::query()
                    ->selectRaw('inquiry_category, COUNT(*) as total')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->groupBy('inquiry_category')
                    ->pluck('total', 'inquiry_category');

                return collect(InquiryCategory::cases())
                    ->map(fn (InquiryCategory $category): array => [
                        'category' => $category->value,
                        'label' => $category->label(),
                        'total' => (int) ($counts[$category->value] ?? 0),
                    ])
                    ->values()
                    ->all();
            },
        );

        return $rows;
    }

    /**
     * @return array{sample_size: int, average_hours: float|null, median_hours: float|null}
     */
    public function getPublishLatencyStats(User $user): array
    {
        if (! $this->canViewPublishReports($user)) {
            return [
                'sample_size' => 0,
                'average_hours' => null,
                'median_hours' => null,
            ];
        }

        $ttl = (int) config('belzamora.reports.cache_ttl_seconds', 60);

        /** @var array{sample_size: int, average_hours: float|null, median_hours: float|null} $stats */
        $stats = Cache::remember(
            PublicCacheInvalidator::ADMIN_REPORTS_PUBLISH_LATENCY,
            $ttl,
            function (): array {
                $hours = ContentApproval::query()
                    ->where('status', ContentApprovalStatus::Published->value)
                    ->whereNotNull('submitted_at')
                    ->whereNotNull('published_at')
                    ->get(['submitted_at', 'published_at'])
                    ->map(function (ContentApproval $approval): float {
                        return abs($approval->published_at->diffInSeconds($approval->submitted_at)) / 3600;
                    })
                    ->sort()
                    ->values();

                if ($hours->isEmpty()) {
                    return [
                        'sample_size' => 0,
                        'average_hours' => null,
                        'median_hours' => null,
                    ];
                }

                return [
                    'sample_size' => $hours->count(),
                    'average_hours' => round((float) $hours->avg(), 2),
                    'median_hours' => round((float) $this->median($hours), 2),
                ];
            },
        );

        return $stats;
    }

    /**
     * @return list<array{model: string, label: string, overdue: int, tracked: int, rate: float}>
     */
    public function getStaleContentBreakdown(User $user): array
    {
        if (! $this->canViewStaleReports($user)) {
            return [];
        }

        $ttl = (int) config('belzamora.reports.cache_ttl_seconds', 60);

        /** @var list<array{model: string, label: string, overdue: int, tracked: int, rate: float}> $rows */
        $rows = Cache::remember(
            PublicCacheInvalidator::ADMIN_REPORTS_STALE_BREAKDOWN,
            $ttl,
            function (): array {
                $models = [
                    'public_pages' => [PublicPage::class, 'Public pages'],
                    'programs' => [Program::class, 'Programs'],
                    'service_guides' => [ServiceGuide::class, 'Service guides'],
                    'legislative_records' => [LegislativeRecord::class, 'Legislation'],
                    'projects' => [Project::class, 'Projects'],
                    'news_records' => [NewsRecord::class, 'News'],
                ];

                $rows = [];

                foreach ($models as $key => [$class, $label]) {
                    $tracked = $class::query()->whereNotNull('review_due_at')->count();
                    $overdue = $class::query()
                        ->whereNotNull('review_due_at')
                        ->where('review_due_at', '<=', now())
                        ->count();

                    $rows[] = [
                        'model' => $key,
                        'label' => $label,
                        'overdue' => $overdue,
                        'tracked' => $tracked,
                        'rate' => $tracked > 0 ? round(($overdue / $tracked) * 100, 1) : 0.0,
                    ];
                }

                $sourceTracked = SourceRecord::query()->whereNotNull('next_review_at')->count();
                $sourceOverdue = SourceRecord::query()
                    ->whereNotNull('next_review_at')
                    ->where('next_review_at', '<=', now())
                    ->count();

                $rows[] = [
                    'model' => 'source_records',
                    'label' => 'Source records',
                    'overdue' => $sourceOverdue,
                    'tracked' => $sourceTracked,
                    'rate' => $sourceTracked > 0 ? round(($sourceOverdue / $sourceTracked) * 100, 1) : 0.0,
                ];

                return $rows;
            },
        );

        return $rows;
    }

    /**
     * @return list<array{barangay: string, status: string, total: int}>
     */
    public function getProjectStatsByBarangay(User $user): array
    {
        if (! $this->canViewProjectReports($user)) {
            return [];
        }

        $ttl = (int) config('belzamora.reports.cache_ttl_seconds', 60);

        /** @var list<array{barangay: string, status: string, total: int}> $rows */
        $rows = Cache::remember(
            PublicCacheInvalidator::ADMIN_REPORTS_PROJECT_BARANGAY,
            $ttl,
            function (): array {
                return Project::query()
                    ->leftJoin('project_statuses', 'projects.project_status_id', '=', 'project_statuses.id')
                    ->select([
                        DB::raw("COALESCE(NULLIF(projects.barangay, ''), 'Unspecified') as barangay"),
                        DB::raw("COALESCE(project_statuses.label, 'Unknown') as status"),
                        DB::raw('COUNT(*) as total'),
                    ])
                    ->groupBy('barangay', 'status')
                    ->orderBy('barangay')
                    ->orderBy('status')
                    ->get()
                    ->map(fn ($row): array => [
                        'barangay' => (string) $row->barangay,
                        'status' => (string) $row->status,
                        'total' => (int) $row->total,
                    ])
                    ->all();
            },
        );

        return $rows;
    }

    /**
     * @param  Collection<int, float>  $values
     */
    private function median(Collection $values): float
    {
        $count = $values->count();

        if ($count === 0) {
            return 0.0;
        }

        $middle = (int) floor(($count - 1) / 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle] + (float) $values[$middle + 1]) / 2;
    }
}

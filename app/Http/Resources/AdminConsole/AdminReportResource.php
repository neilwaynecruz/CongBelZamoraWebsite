<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryStatus;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\ServiceGuide;
use App\Models\User;
use App\Services\AdminReportsMetricsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminReportResource
{
    public function __construct(
        private readonly User $user,
        private readonly AdminReportsMetricsService $metrics,
        private readonly int $days = 30,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => [
                'days' => $this->days,
                'label' => 'Last '.$this->days.' days',
            ],
            'can' => [
                'viewInquiryReports' => $this->metrics->canViewInquiryReports($this->user),
                'viewPublishReports' => $this->metrics->canViewPublishReports($this->user),
                'viewStaleReports' => $this->metrics->canViewStaleReports($this->user),
                'viewProjectReports' => $this->metrics->canViewProjectReports($this->user),
            ],
            'inquiries' => [
                'volumeByCategory' => $this->metrics->getInquiryVolumeByCategory($this->user, $this->days),
                'caseMetrics' => $this->metrics->getInquiryCaseMetrics($this->user, $this->days),
                'summary' => $this->inquirySummary(),
            ],
            'publishing' => [
                'latency' => $this->metrics->getPublishLatencyStats($this->user),
                'approvalSummary' => $this->approvalSummary(),
            ],
            'freshness' => [
                'breakdown' => $this->metrics->getStaleContentBreakdown($this->user),
                'contentSummary' => $this->contentSummary(),
            ],
            'projects' => [
                'byBarangay' => $this->metrics->getProjectStatsByBarangay($this->user),
            ],
            'exports' => [
                'enabled' => false,
                'reason' => 'Custom exports remain disabled until privacy-reviewed export policies are approved for this console.',
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function inquirySummary(): array
    {
        if (! $this->metrics->canViewInquiryReports($this->user)) {
            return [
                'total' => 0,
                'open' => 0,
                'resolved' => 0,
                'closed' => 0,
            ];
        }

        return [
            'total' => Inquiry::query()->count(),
            'open' => Inquiry::query()
                ->whereIn('status', [InquiryStatus::Pending->value, InquiryStatus::UnderReview->value])
                ->count(),
            'resolved' => Inquiry::query()->where('status', InquiryStatus::Resolved->value)->count(),
            'closed' => Inquiry::query()->where('status', InquiryStatus::Closed->value)->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function approvalSummary(): array
    {
        if (! $this->metrics->canViewPublishReports($this->user)) {
            return [
                'total' => 0,
                'forReview' => 0,
                'approved' => 0,
                'published' => 0,
                'needsRevision' => 0,
            ];
        }

        return [
            'total' => ContentApproval::query()->count(),
            'forReview' => ContentApproval::query()->where('status', ContentApprovalStatus::ForReview->value)->count(),
            'approved' => ContentApproval::query()->where('status', ContentApprovalStatus::Approved->value)->count(),
            'published' => ContentApproval::query()->where('status', ContentApprovalStatus::Published->value)->count(),
            'needsRevision' => ContentApproval::query()->where('status', ContentApprovalStatus::NeedsRevision->value)->count(),
        ];
    }

    /**
     * @return list<array{key: string, label: string, total: int, published: int, reviewDue: int}>
     */
    private function contentSummary(): array
    {
        if (! $this->metrics->canViewStaleReports($this->user)) {
            return [];
        }

        return [
            $this->contentRow('public_pages', 'Public pages', PublicPage::query()),
            $this->contentRow('programs', 'Programs', Program::query()),
            $this->contentRow('service_guides', 'Service guides', ServiceGuide::query()),
            $this->contentRow('legislation', 'Legislation', LegislativeRecord::query()),
            $this->contentRow('projects', 'Projects', Project::query()),
            $this->contentRow('news', 'News/media', NewsRecord::query()),
        ];
    }

    /**
     * @param  Builder<*>  $query
     * @return array{key: string, label: string, total: int, published: int, reviewDue: int}
     */
    private function contentRow(string $key, string $label, Builder $query): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'total' => (clone $query)->count(),
            'published' => (clone $query)->whereNotNull('published_at')->count(),
            'reviewDue' => (clone $query)
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now())
                ->count(),
        ];
    }
}

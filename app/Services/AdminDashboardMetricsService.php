<?php

namespace App\Services;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryStatus;
use App\Enums\ProgramStatus;
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
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @phpstan-type NeedsAttentionItem array{type: string, title: string, status: string, date: DateTimeInterface, severity: 'danger'|'warning', url: string}
 */
class AdminDashboardMetricsService
{
    /**
     * Get non-sensitive dashboard counts, cached for 30 seconds.
     *
     * @return array<string, int>
     */
    public function getCachedCounts(): array
    {
        return Cache::remember('admin_dashboard_counts', 30, function (): array {
            return [
                'open_programs' => Program::query()->where('status', ProgramStatus::Open->value)->count(),
                'pending_approvals' => ContentApproval::query()->where('status', ContentApprovalStatus::ForReview->value)->count(),
                'stale_content' => $this->getStaleContentCount(),
                'sources_due' => SourceRecord::query()
                    ->whereNotNull('next_review_at')
                    ->where('next_review_at', '<=', now())
                    ->count(),
            ];
        });
    }

    /**
     * Get total stale content count.
     */
    public function getStaleContentCount(): int
    {
        return (int) collect([
            PublicPage::class,
            Program::class,
            ServiceGuide::class,
            LegislativeRecord::class,
            Project::class,
            NewsRecord::class,
        ])->sum(fn (string $model): int => $model::query()
            ->whereNotNull('review_due_at')
            ->where('review_due_at', '<=', now())
            ->count());
    }

    /**
     * Get unanswered inquiries count, checking user permissions.
     */
    public function getUnansweredInquiriesCount(User $user): int
    {
        if (! $user->canManageInquiries() && ! $user->canAuditRecords()) {
            return 0;
        }

        return Inquiry::query()
            ->whereIn('status', [
                InquiryStatus::Pending->value,
                InquiryStatus::UnderReview->value,
            ])
            ->whereNull('first_responded_at')
            ->whereNull('responded_at')
            ->count();
    }

    /**
     * @return array{assigned_open: int, assigned_overdue: int, unassigned_open: int, resolved_today: int}
     */
    public function getInquiryQueueCounts(User $user): array
    {
        if (! $user->canManageInquiries() && ! $user->canAuditRecords()) {
            return [
                'assigned_open' => 0,
                'assigned_overdue' => 0,
                'unassigned_open' => 0,
                'resolved_today' => 0,
            ];
        }

        $base = Inquiry::query();

        if ($user->hasAdminRole(AdminRole::InquiryOfficer)) {
            $base->where(function ($query) use ($user): void {
                $query
                    ->whereNull('assigned_officer_id')
                    ->orWhere('assigned_officer_id', $user->id);
            });
        }

        return [
            'assigned_open' => (clone $base)
                ->openCase()
                ->where('assigned_officer_id', $user->id)
                ->count(),
            'assigned_overdue' => (clone $base)
                ->overdueFirstResponse()
                ->where('assigned_officer_id', $user->id)
                ->count(),
            'unassigned_open' => (clone $base)
                ->openCase()
                ->whereNull('assigned_officer_id')
                ->count(),
            'resolved_today' => (clone $base)
                ->where('status', InquiryStatus::Resolved->value)
                ->whereDate('resolved_at', now('Asia/Manila')->toDateString())
                ->count(),
        ];
    }

    public function canViewNeedsAttention(User $user): bool
    {
        return $this->canViewContentHealth($user)
            || $user->canManageInquiries()
            || $user->canAuditRecords();
    }

    public function canViewContentHealth(User $user): bool
    {
        return $user->canReviewPublicContent()
            || $user->canManagePrograms()
            || $user->canManageLegislation()
            || $user->canManageProjects()
            || $user->canManageNewsMedia()
            || $user->canAuditRecords();
    }

    /**
     * Get list of items needing attention (overdue content, pending approvals, due sources, and inquiries).
     *
     * @return list<NeedsAttentionItem>
     */
    public function getNeedsAttentionItems(User $user): array
    {
        /** @var list<NeedsAttentionItem> $items */
        $items = [];

        foreach ($this->staleContentModelsFor($user) as $label => $class) {
            $records = $class::query()
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now())
                ->orderBy('review_due_at', 'asc')
                ->limit(5)
                ->get();

            foreach ($records as $record) {
                if (! $user->can('view', $record)) {
                    continue;
                }

                $items[] = [
                    'type' => $label,
                    'title' => $this->recordTitle($record),
                    'status' => 'Stale Review',
                    'date' => $this->recordDate($record, 'review_due_at'),
                    'severity' => 'warning',
                    'url' => $this->recordUrl($record),
                ];
            }
        }

        // 2. Pending approvals (visible if user can review public content)
        if ($user->canReviewPublicContent()) {
            $approvals = ContentApproval::query()
                ->with('approvable')
                ->where('status', ContentApprovalStatus::ForReview->value)
                ->orderBy('submitted_at', 'asc')
                ->limit(5)
                ->get();

            foreach ($approvals as $approval) {
                if (! $approval instanceof Model || ! $approval->approvable instanceof Model || ! $user->can('view', $approval->approvable)) {
                    continue;
                }

                $items[] = [
                    'type' => 'Approval',
                    'title' => 'Approval for '.$this->readableRecordType($approval->approvable).': '.$this->recordTitle($approval->approvable),
                    'status' => 'Pending Review',
                    'date' => $this->recordDate($approval, 'submitted_at'),
                    'severity' => 'danger',
                    'url' => route('admin-console.content-approvals.show', $approval),
                ];
            }
        }

        $dueSources = SourceRecord::query()
            ->with('sourceable')
            ->whereNotNull('next_review_at')
            ->where('next_review_at', '<=', now())
            ->orderBy('next_review_at', 'asc')
            ->limit(5)
            ->get();

        foreach ($dueSources as $source) {
            if (! $source->sourceable instanceof Model || ! $user->can('view', $source->sourceable)) {
                continue;
            }

            $items[] = [
                'type' => 'Source',
                'title' => 'Source for '.$this->readableRecordType($source->sourceable).': '.$this->recordTitle($source->sourceable),
                'status' => 'Verification Due',
                'date' => $this->recordDate($source, 'next_review_at'),
                'severity' => 'warning',
                'url' => $this->recordUrl($source->sourceable),
            ];
        }

        if ($user->canManageInquiries() || $user->canAuditRecords()) {
            $slaHours = max(1, (int) config('belzamora.inquiries.unanswered_sla_hours', 48));

            $inquiries = Inquiry::query()
                ->overdueFirstResponse()
                ->orderBy('created_at', 'asc')
                ->limit(5)
                ->get();

            foreach ($inquiries as $inquiry) {
                if (! $user->can('view', $inquiry)) {
                    continue;
                }

                $items[] = [
                    'type' => 'Inquiry',
                    'title' => "Inquiry {$inquiry->reference_code} awaiting response",
                    'status' => 'Overdue first response (>'.$slaHours.'h target)',
                    'date' => $this->recordDate($inquiry, 'first_response_due_at'),
                    'severity' => 'danger',
                    'url' => route('admin-console.inquiries.show', $inquiry),
                ];
            }
        }

        // Sort items: show danger (red) first, then warnings (amber/gold), ordered by date ascending (oldest/most urgent first)
        usort($items, function (array $a, array $b): int {
            if ($a['severity'] !== $b['severity']) {
                return $a['severity'] === 'danger' ? -1 : 1;
            }

            return $a['date'] <=> $b['date'];
        });

        return $items;
    }

    /**
     * @return array<string, class-string<Model>>
     */
    private function staleContentModelsFor(User $user): array
    {
        $models = [];

        if ($user->canReviewPublicContent() || $user->canManageNewsMedia() || $user->canAuditRecords()) {
            $models['Public Page'] = PublicPage::class;
        }

        if ($user->canReviewPublicContent() || $user->canManagePrograms() || $user->canAuditRecords()) {
            $models['Program'] = Program::class;
            $models['Service Guide'] = ServiceGuide::class;
        }

        if ($user->canReviewPublicContent() || $user->canManageLegislation() || $user->canAuditRecords()) {
            $models['Legislative Record'] = LegislativeRecord::class;
        }

        if ($user->canReviewPublicContent() || $user->canManageProjects() || $user->canAuditRecords()) {
            $models['Project'] = Project::class;
        }

        if ($user->canReviewPublicContent() || $user->canManageNewsMedia() || $user->canAuditRecords()) {
            $models['News Record'] = NewsRecord::class;
        }

        return $models;
    }

    private function recordUrl(Model $record): string
    {
        return match ($record::class) {
            PublicPage::class => route('admin-console.public-pages.show', $record),
            Program::class => route('admin-console.programs.show', $record),
            ServiceGuide::class => route('admin-console.programs.index', ['search' => $this->recordTitle($record)]),
            LegislativeRecord::class => route('admin-console.legislation.show', $record),
            Project::class => route('admin-console.projects.show', $record),
            NewsRecord::class => route('admin-console.news.show', $record),
            default => route('admin-console.dashboard'),
        };
    }

    private function readableRecordType(Model $record): string
    {
        return (string) str(class_basename($record))->headline();
    }

    private function recordTitle(Model $record): string
    {
        foreach (['title', 'public_title', 'official_title', 'label', 'name', 'reference_code', 'bill_number'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $this->readableRecordType($record).' #'.$record->getKey();
    }

    private function recordDate(object $record, string $attribute): DateTimeInterface
    {
        $value = $record instanceof Model
            ? $record->getAttribute($attribute)
            : (property_exists($record, $attribute) ? $record->{$attribute} : null);

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return CarbonImmutable::parse($value);
        }

        return CarbonImmutable::now();
    }
}

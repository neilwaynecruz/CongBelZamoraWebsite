<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryStatus;
use App\Enums\ProgramStatus;
use App\Filament\Resources\ContentApprovalResource;
use App\Filament\Resources\InquiryResource;
use App\Filament\Resources\LegislativeRecordResource;
use App\Filament\Resources\NewsRecordResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\PublicPageResource;
use App\Filament\Resources\ServiceGuideResource;
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
use Filament\Resources\Resource;
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
            ->whereNull('responded_at')
            ->count();
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

        foreach ($this->staleContentModelsFor($user) as $label => [$class, $resource]) {
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
                    'url' => $this->recordUrl($resource, $record, $user),
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
                if (! $approval->approvable instanceof Model || ! $user->can('view', $approval->approvable)) {
                    continue;
                }

                $resource = $this->resourceFor($approval->approvable);

                if ($resource === null) {
                    continue;
                }

                $items[] = [
                    'type' => 'Approval',
                    'title' => 'Approval for '.$this->readableRecordType($approval->approvable).': '.$this->recordTitle($approval->approvable),
                    'status' => 'Pending Review',
                    'date' => $this->recordDate($approval, 'submitted_at'),
                    'severity' => 'danger',
                    'url' => data_get($approval->metadata, 'launch_gate') === true
                        ? $this->recordUrl(ContentApprovalResource::class, $approval, $user)
                        : $this->recordUrl($resource, $approval->approvable, $user),
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

            $resource = $this->resourceFor($source->sourceable);

            if ($resource === null) {
                continue;
            }

            $items[] = [
                'type' => 'Source',
                'title' => 'Source for '.$this->readableRecordType($source->sourceable).': '.$this->recordTitle($source->sourceable),
                'status' => 'Verification Due',
                'date' => $this->recordDate($source, 'next_review_at'),
                'severity' => 'warning',
                'url' => $this->recordUrl($resource, $source->sourceable, $user),
            ];
        }

        if ($user->canManageInquiries() || $user->canAuditRecords()) {
            $slaHours = max(1, (int) config('belzamora.inquiries.unanswered_sla_hours', 48));

            $inquiries = Inquiry::query()
                ->whereIn('status', [
                    InquiryStatus::Pending->value,
                    InquiryStatus::UnderReview->value,
                ])
                ->whereNull('responded_at')
                ->whereNull('resolved_at')
                ->where('created_at', '<=', now()->subHours($slaHours))
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
                    'status' => 'Overdue Answer (>'.$slaHours.'h)',
                    'date' => $this->recordDate($inquiry, 'created_at'),
                    'severity' => 'danger',
                    'url' => $this->recordUrl(InquiryResource::class, $inquiry, $user),
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
     * @return array<string, array{0: class-string<Model>, 1: class-string<resource>}>
     */
    private function staleContentModelsFor(User $user): array
    {
        $models = [];

        if ($user->canReviewPublicContent() || $user->canManageNewsMedia() || $user->canAuditRecords()) {
            $models['Public Page'] = [PublicPage::class, PublicPageResource::class];
        }

        if ($user->canReviewPublicContent() || $user->canManagePrograms() || $user->canAuditRecords()) {
            $models['Program'] = [Program::class, ProgramResource::class];
            $models['Service Guide'] = [ServiceGuide::class, ServiceGuideResource::class];
        }

        if ($user->canReviewPublicContent() || $user->canManageLegislation() || $user->canAuditRecords()) {
            $models['Legislative Record'] = [LegislativeRecord::class, LegislativeRecordResource::class];
        }

        if ($user->canReviewPublicContent() || $user->canManageProjects() || $user->canAuditRecords()) {
            $models['Project'] = [Project::class, ProjectResource::class];
        }

        if ($user->canReviewPublicContent() || $user->canManageNewsMedia() || $user->canAuditRecords()) {
            $models['News Record'] = [NewsRecord::class, NewsRecordResource::class];
        }

        return $models;
    }

    /**
     * @return class-string<resource>|null
     */
    private function resourceFor(Model $record): ?string
    {
        return match ($record::class) {
            PublicPage::class => PublicPageResource::class,
            Program::class => ProgramResource::class,
            ServiceGuide::class => ServiceGuideResource::class,
            LegislativeRecord::class => LegislativeRecordResource::class,
            Project::class => ProjectResource::class,
            NewsRecord::class => NewsRecordResource::class,
            Inquiry::class => InquiryResource::class,
            default => null,
        };
    }

    /**
     * @param  class-string<resource>  $resource
     */
    private function recordUrl(string $resource, Model $record, User $user): string
    {
        $page = $user->can('update', $record) ? 'edit' : 'view';

        return $resource::getUrl($page, ['record' => $record]);
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

    private function recordDate(Model $record, string $attribute): DateTimeInterface
    {
        $value = $record->getAttribute($attribute);

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return CarbonImmutable::parse($value);
        }

        return CarbonImmutable::now();
    }
}

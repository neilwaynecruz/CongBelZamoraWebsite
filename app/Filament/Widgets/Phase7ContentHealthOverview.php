<?php

namespace App\Filament\Widgets;

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
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Phase7ContentHealthOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Open programs', Program::query()->where('status', ProgramStatus::Open->value)->count()),
            Stat::make('Pending approvals', ContentApproval::query()->where('status', ContentApprovalStatus::ForReview->value)->count()),
            Stat::make('Unanswered inquiries', Inquiry::query()->whereIn('status', [
                InquiryStatus::Pending->value,
                InquiryStatus::UnderReview->value,
            ])->whereNull('responded_at')->count()),
            Stat::make('Stale content', $this->staleContentCount())
                ->description('Review due date has passed'),
            Stat::make('Sources due', SourceRecord::query()
                ->whereNotNull('next_review_at')
                ->where('next_review_at', '<=', now())
                ->count()),
        ];
    }

    private function staleContentCount(): int
    {
        return collect([
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
}

<?php

namespace App\Jobs;

use App\Enums\AdminRole;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\ServiceGuide;
use App\Models\SourceRecord;
use App\Models\User;
use App\Notifications\StaleContentDigestNotification;
use App\Services\AuditLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class FlagStaleContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AuditLogService $auditLog): void
    {
        $counts = [];

        foreach ($this->reviewableModels() as $label => $modelClass) {
            $records = $modelClass::query()
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now())
                ->get();

            $counts[$label] = $records->count();

            $records->each(fn (Model $record): mixed => $auditLog->record(
                event: 'content.review_overdue',
                auditable: $record,
                metadata: [
                    'model' => $record::class,
                    'review_due_at' => (string) $record->getAttribute('review_due_at'),
                ],
            ));
        }

        $sourceRecords = SourceRecord::query()
            ->whereNotNull('next_review_at')
            ->where('next_review_at', '<=', now())
            ->get();

        $counts['source_records'] = $sourceRecords->count();

        $sourceRecords->each(fn (SourceRecord $record): mixed => $auditLog->record(
            event: 'source_record.review_overdue',
            auditable: $record,
            metadata: [
                'source_title' => $record->title,
                'next_review_at' => (string) $record->next_review_at,
            ],
        ));

        if (array_sum($counts) > 0) {
            Notification::send($this->recipients(), new StaleContentDigestNotification($counts));
        }
    }

    /**
     * @return array<string, class-string<Model>>
     */
    private function reviewableModels(): array
    {
        return [
            'public_pages' => PublicPage::class,
            'programs' => Program::class,
            'service_guides' => ServiceGuide::class,
            'legislative_records' => LegislativeRecord::class,
            'projects' => Project::class,
            'news_records' => NewsRecord::class,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    private function recipients(): Collection
    {
        return User::query()
            ->whereIn('admin_role', [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
                AdminRole::ReviewerApprover->value,
                AdminRole::ReadOnlyAuditor->value,
            ])
            ->get();
    }
}

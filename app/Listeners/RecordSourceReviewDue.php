<?php

namespace App\Listeners;

use App\Events\SourceReviewDue;
use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;

class RecordSourceReviewDue
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly PublicCacheInvalidator $invalidator,
    ) {}

    public function handle(SourceReviewDue $event): void
    {
        $record = $event->sourceRecord;

        $this->auditLog->record(
            event: 'source_record.review_overdue',
            auditable: $record,
            metadata: [
                'source_title' => $record->title,
                'next_review_at' => (string) $record->next_review_at,
            ],
        );

        $this->invalidator->forgetAdminDashboardCounts();
    }
}

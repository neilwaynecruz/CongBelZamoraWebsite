<?php

namespace App\Filament\Resources\ProjectEvidenceResource\Pages;

use App\Filament\Resources\ProjectEvidenceResource;
use App\Models\ProjectEvidence;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateProjectEvidence extends CreateRecord
{
    protected static string $resource = ProjectEvidenceResource::class;

    protected function afterCreate(): void
    {
        /** @var ProjectEvidence $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'project_evidence.created',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'project_id' => $record->project_id,
                'title' => $record->title,
                'source_url' => $record->source_url,
            ],
        );
    }
}

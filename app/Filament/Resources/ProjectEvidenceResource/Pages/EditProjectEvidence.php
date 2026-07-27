<?php

namespace App\Filament\Resources\ProjectEvidenceResource\Pages;

use App\Filament\Resources\ProjectEvidenceResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\ProjectEvidence;
use App\Services\AuditLogService;
use Filament\Resources\Pages\EditRecord;

class EditProjectEvidence extends EditRecord
{
    use SoftDeleteHeaderActions;

    /** @return array<int, \\Filament\\Actions\\Action> */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected static string $resource = ProjectEvidenceResource::class;

    protected function afterSave(): void
    {
        /** @var ProjectEvidence $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'project_evidence.updated',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'project_id' => $record->project_id,
                'title' => $record->title,
                'verified_at' => (string) $record->verified_at,
            ],
        );

        app(\App\Services\PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

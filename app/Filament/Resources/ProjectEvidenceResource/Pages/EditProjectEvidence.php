<?php

namespace App\Filament\Resources\ProjectEvidenceResource\Pages;

use App\Filament\Resources\ProjectEvidenceResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\ProjectEvidence;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProjectEvidence extends EditRecord
{
    use SoftDeleteHeaderActions;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected static string $resource = ProjectEvidenceResource::class;

    protected function afterSave(): void
    {
        /** @var ProjectEvidence $record */
        $record = $this->record;
        $actor = Auth::user();

        app(AuditLogService::class)->record(
            event: 'project_evidence.updated',
            auditable: $record,
            actor: $actor instanceof User ? $actor : null,
            request: request(),
            newValues: [
                'project_id' => $record->project_id,
                'title' => $record->title,
                'verified_at' => (string) $record->verified_at,
            ],
        );

        app(PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

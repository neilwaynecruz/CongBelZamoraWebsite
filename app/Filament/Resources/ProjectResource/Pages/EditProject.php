<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Services\AuditLogService;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterSave(): void
    {
        /** @var Project $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'project.updated',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'status' => $record->status->value->value,
                'published_at' => (string) $record->published_at,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );
    }
}

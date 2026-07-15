<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        /** @var Project $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'project.created',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'status' => $record->status->value->value,
                'published_at' => (string) $record->published_at,
            ],
        );
    }
}

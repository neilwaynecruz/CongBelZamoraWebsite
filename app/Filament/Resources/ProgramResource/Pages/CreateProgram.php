<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use App\Models\Program;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateProgram extends CreateRecord
{
    protected static string $resource = ProgramResource::class;

    protected function afterCreate(): void
    {
        /** @var Program $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'program.created',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'status' => $record->status->value,
                'published_at' => (string) $record->published_at,
            ],
        );
    }
}

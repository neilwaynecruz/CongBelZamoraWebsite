<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\Program;
use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditProgram extends EditRecord
{
    use SoftDeleteHeaderActions;

    protected static string $resource = ProgramResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected function afterSave(): void
    {
        /** @var Program $record */
        $record = $this->record;

        app(AuditLogService::class)->record(
            event: 'program.updated',
            auditable: $record,
            actor: auth()->user(),
            request: request(),
            newValues: [
                'title' => $record->title,
                'slug' => $record->slug,
                'status' => $record->status->value,
                'published_at' => (string) $record->published_at,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );

        app(PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

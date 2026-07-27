<?php

namespace App\Filament\Resources\LegislativeRecordResource\Pages;

use App\Filament\Resources\LegislativeRecordResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\LegislativeRecord;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditLegislativeRecord extends EditRecord
{
    use SoftDeleteHeaderActions;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected static string $resource = LegislativeRecordResource::class;

    protected function afterSave(): void
    {
        /** @var LegislativeRecord $record */
        $record = $this->record;
        $actor = Auth::user();

        app(AuditLogService::class)->record(
            event: 'legislative_record.updated',
            auditable: $record,
            actor: $actor instanceof User ? $actor : null,
            request: request(),
            newValues: [
                'bill_number' => $record->bill_number,
                'public_title' => $record->public_title,
                'published_at' => (string) $record->published_at,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );

        app(PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

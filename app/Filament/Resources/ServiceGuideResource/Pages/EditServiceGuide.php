<?php

namespace App\Filament\Resources\ServiceGuideResource\Pages;

use App\Filament\Resources\ServiceGuideResource;
use App\Filament\Support\SoftDeleteHeaderActions;
use App\Models\ServiceGuide;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditServiceGuide extends EditRecord
{
    use SoftDeleteHeaderActions;

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return $this->softDeleteHeaderActions();
    }

    protected static string $resource = ServiceGuideResource::class;

    protected function afterSave(): void
    {
        /** @var ServiceGuide $record */
        $record = $this->record;
        $actor = Auth::user();

        app(AuditLogService::class)->record(
            event: 'service_guide.updated',
            auditable: $record,
            actor: $actor instanceof User ? $actor : null,
            request: request(),
            newValues: [
                'label' => $record->label,
                'slug' => $record->slug,
                'responsible_office_type' => $record->responsible_office_type->value,
                'published_at' => (string) $record->published_at,
                'review_due_at' => (string) $record->review_due_at,
            ],
        );

        app(PublicCacheInvalidator::class)->forgetForModel($record);
    }
}

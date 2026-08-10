<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminCivicContentLifecycleService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    public function archive(Model $record, User $actor, Request $request, string $reason): void
    {
        DB::transaction(function () use ($record, $actor, $request, $reason): void {
            if ($this->isTrashed($record)) {
                return;
            }

            $record->delete();

            $this->auditLog->record(
                event: $this->eventName($record, 'archived'),
                auditable: $record,
                actor: $actor,
                request: $request,
                oldValues: ['deleted_at' => null],
                newValues: ['deleted_at' => now()->toISOString()],
                metadata: ['reason' => $reason],
            );
        });
    }

    public function restore(Model $record, User $actor, Request $request, ?string $reason = null): void
    {
        DB::transaction(function () use ($record, $actor, $request, $reason): void {
            if (! $this->isTrashed($record)) {
                return;
            }

            $record->restore();

            $this->auditLog->record(
                event: $this->eventName($record, 'restored'),
                auditable: $record,
                actor: $actor,
                request: $request,
                oldValues: ['deleted_at' => $record->getOriginal('deleted_at')],
                newValues: ['deleted_at' => null],
                metadata: ['reason' => $reason],
            );
        });
    }

    public function forceDelete(
        Model $record,
        User $actor,
        Request $request,
        string $confirmation,
        string $reason,
    ): void {
        $title = $this->title($record);

        if (! hash_equals($title, $confirmation)) {
            throw ValidationException::withMessages([
                'confirmation' => 'Type the exact record title to permanently delete this item.',
            ]);
        }

        DB::transaction(function () use ($record, $actor, $request, $reason, $title): void {
            $id = $record->getKey();
            $type = $record->getMorphClass();
            $event = $this->eventName($record, 'permanently_deleted');

            $this->auditLog->record(
                event: $event,
                auditable: $record,
                actor: $actor,
                request: $request,
                oldValues: ['id' => $id, 'title' => $title],
                newValues: null,
                metadata: ['reason' => $reason],
            );

            $record->forceDelete();

            $this->auditLog->record(
                event: $event.'.confirmed',
                actor: $actor,
                request: $request,
                metadata: [
                    'auditable_type' => $type,
                    'auditable_id' => $id,
                ],
            );
        });
    }

    private function eventName(Model $record, string $action): string
    {
        $base = str($record->getTable())
            ->singular()
            ->replace('_', '.')
            ->toString();

        return $base.'.'.$action;
    }

    private function isTrashed(Model $record): bool
    {
        return method_exists($record, 'trashed') && $record->trashed();
    }

    private function title(Model $record): string
    {
        $title = $record->getAttribute('title');

        return is_string($title) && $title !== ''
            ? $title
            : (string) $record->getKey();
    }
}

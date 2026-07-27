<?php

namespace App\Observers;

use App\Services\AuditLogService;
use App\Services\PublicCacheInvalidator;
use Illuminate\Database\Eloquent\Model;

class CivicContentLifecycleObserver
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly PublicCacheInvalidator $cacheInvalidator,
    ) {}

    public function deleted(Model $model): void
    {
        $this->auditLog->record(
            event: $this->isForceDeleting($model) ? 'force_deleted' : 'deleted',
            auditable: $model,
            newValues: [
                'id' => $model->getKey(),
                'class' => $model::class,
            ],
        );

        $this->cacheInvalidator->forgetForModel($model);
    }

    public function restored(Model $model): void
    {
        $this->auditLog->record(
            event: 'restored',
            auditable: $model,
            newValues: [
                'id' => $model->getKey(),
                'class' => $model::class,
            ],
        );

        $this->cacheInvalidator->forgetForModel($model);
    }

    private function isForceDeleting(Model $model): bool
    {
        return method_exists($model, 'isForceDeleting') && $model->isForceDeleting();
    }
}

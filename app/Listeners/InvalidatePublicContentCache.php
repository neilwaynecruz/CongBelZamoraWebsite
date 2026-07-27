<?php

namespace App\Listeners;

use App\Events\ContentPublished;
use App\Events\ContentScheduled;
use App\Services\PublicCacheInvalidator;

class InvalidatePublicContentCache
{
    public function __construct(private readonly PublicCacheInvalidator $invalidator) {}

    public function handle(ContentPublished|ContentScheduled $event): void
    {
        $this->invalidator->forgetForModel($event->approval->approvable);
    }
}

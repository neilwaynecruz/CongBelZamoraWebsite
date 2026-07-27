<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

trait ClearsPublicContentCache
{
    protected function clearPublicContentCache(?Model $record = null): void
    {
        app(\App\Services\PublicCacheInvalidator::class)->forgetForModel(
            $record ?? (property_exists($this, 'record') ? $this->record : null),
        );
    }
}

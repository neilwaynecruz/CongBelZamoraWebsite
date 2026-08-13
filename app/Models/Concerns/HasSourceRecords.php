<?php

namespace App\Models\Concerns;

use App\Models\SourceRecord;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSourceRecords
{
    /**
     * @return MorphMany<SourceRecord, $this>
     */
    public function sourceRecords(): MorphMany
    {
        return $this->morphMany(SourceRecord::class, 'sourceable');
    }
}

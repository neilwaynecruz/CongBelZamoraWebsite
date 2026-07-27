<?php

namespace App\Models;

use Database\Factories\ServiceGuideStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceGuideStep extends Model
{
    /** @use HasFactory<ServiceGuideStepFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'service_guide_id',
        'section',
        'item_text',
        'sort_order',
    ];

    /**
     * @return BelongsTo<ServiceGuide, $this>
     */
    public function serviceGuide(): BelongsTo
    {
        return $this->belongsTo(ServiceGuide::class);
    }
}

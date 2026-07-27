<?php

namespace App\Models;

use Database\Factories\LegislativeSourceHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegislativeSourceHistory extends Model
{
    /** @use HasFactory<LegislativeSourceHistoryFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'legislative_record_id',
        'source_label',
        'source_url',
        'status_snapshot',
        'checked_by_id',
        'checked_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<LegislativeRecord, $this>
     */
    public function legislativeRecord(): BelongsTo
    {
        return $this->belongsTo(LegislativeRecord::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_id');
    }
}

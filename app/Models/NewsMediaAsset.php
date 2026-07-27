<?php

namespace App\Models;

use Database\Factories\NewsMediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsMediaAsset extends Model
{
    /** @use HasFactory<NewsMediaAssetFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'news_record_id',
        'title',
        'media_type',
        'media_path',
        'source_label',
        'source_url',
        'caption',
        'alt_text',
        'rights_note',
        'verified_at',
        'verified_by_id',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<NewsRecord, $this>
     */
    public function newsRecord(): BelongsTo
    {
        return $this->belongsTo(NewsRecord::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }
}

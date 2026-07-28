<?php

namespace App\Models;

use App\Enums\InquiryActivityType;
use Database\Factories\InquiryActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property InquiryActivityType $type
 * @property array<string, mixed>|null $metadata
 */
class InquiryActivity extends Model
{
    /** @use HasFactory<InquiryActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'actor_id',
        'type',
        'visibility',
        'body',
        'old_status',
        'new_status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => InquiryActivityType::class,
            'body' => 'encrypted',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Inquiry, $this>
     */
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

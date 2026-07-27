<?php

namespace App\Models;

use App\Enums\ContactPreference;
use App\Enums\InquiryCategory;
use App\Enums\InquiryStatus;
use Carbon\CarbonImmutable;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $reference_code
 * @property string $citizen_name
 * @property ContactPreference $contact_preference
 * @property string|null $citizen_email
 * @property string|null $citizen_mobile
 * @property InquiryCategory $inquiry_category
 * @property string $message_body
 * @property InquiryStatus $status
 * @property int|null $assigned_officer_id
 * @property CarbonImmutable|null $privacy_consent_at
 * @property CarbonImmutable|null $responded_at
 * @property CarbonImmutable|null $resolved_at
 * @property CarbonImmutable|null $retention_expires_at
 * @property CarbonImmutable|null $anonymized_at
 * @property array<string, mixed>|null $routing_notes
 * @property string|null $internal_notes
 * @property array<string, mixed>|null $metadata
 * @property CarbonImmutable|null $created_at
 */
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'citizen_name',
        'contact_preference',
        'citizen_email',
        'citizen_mobile',
        'inquiry_category',
        'message_body',
        'status',
        'assigned_officer_id',
        'privacy_consent_at',
        'responded_at',
        'resolved_at',
        'retention_expires_at',
        'anonymized_at',
        'routing_notes',
        'internal_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'citizen_name' => 'encrypted',
            'contact_preference' => ContactPreference::class,
            'citizen_email' => 'encrypted',
            'citizen_mobile' => 'encrypted',
            'inquiry_category' => InquiryCategory::class,
            'message_body' => 'encrypted',
            'status' => InquiryStatus::class,
            'privacy_consent_at' => 'immutable_datetime',
            'responded_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'retention_expires_at' => 'immutable_datetime',
            'anonymized_at' => 'immutable_datetime',
            'routing_notes' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    /**
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    public function scopeUnexpired(Builder $query): Builder
    {
        return $query->whereNull('anonymized_at');
    }

    /**
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    public function scopeExpiredForRetention(Builder $query): Builder
    {
        return $query
            ->whereNull('anonymized_at')
            ->where('retention_expires_at', '<=', now());
    }
}

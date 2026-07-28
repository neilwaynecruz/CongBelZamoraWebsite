<?php

namespace App\Models;

use App\Enums\ContactPreference;
use App\Enums\InquiryCategory;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use Carbon\CarbonImmutable;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $reference_code
 * @property string $citizen_name
 * @property ContactPreference $contact_preference
 * @property string|null $citizen_email
 * @property string|null $citizen_mobile
 * @property InquiryCategory $inquiry_category
 * @property InquiryPriority $priority
 * @property string $message_body
 * @property InquiryStatus $status
 * @property int|null $assigned_officer_id
 * @property string|null $office_unit
 * @property CarbonImmutable|null $assigned_at
 * @property CarbonImmutable|null $privacy_consent_at
 * @property CarbonImmutable|null $first_response_due_at
 * @property CarbonImmutable|null $responded_at
 * @property CarbonImmutable|null $first_responded_at
 * @property CarbonImmutable|null $resolved_at
 * @property CarbonImmutable|null $last_internal_activity_at
 * @property CarbonImmutable|null $closed_at
 * @property string|null $closed_reason
 * @property CarbonImmutable|null $overdue_reminder_sent_at
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
        'office_unit',
        'message_body',
        'status',
        'priority',
        'assigned_officer_id',
        'assigned_at',
        'privacy_consent_at',
        'first_response_due_at',
        'responded_at',
        'first_responded_at',
        'resolved_at',
        'last_internal_activity_at',
        'closed_at',
        'closed_reason',
        'overdue_reminder_sent_at',
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
            'priority' => InquiryPriority::class,
            'message_body' => 'encrypted',
            'status' => InquiryStatus::class,
            'assigned_at' => 'immutable_datetime',
            'privacy_consent_at' => 'immutable_datetime',
            'first_response_due_at' => 'immutable_datetime',
            'responded_at' => 'immutable_datetime',
            'first_responded_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'last_internal_activity_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'overdue_reminder_sent_at' => 'immutable_datetime',
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
     * @return HasMany<InquiryActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(InquiryActivity::class)->latest();
    }

    /**
     * @return HasMany<InquiryActivity, $this>
     */
    public function chronologicalActivities(): HasMany
    {
        return $this->hasMany(InquiryActivity::class)->oldest();
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

    /**
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    public function scopeOpenCase(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InquiryStatus::Pending->value,
            InquiryStatus::UnderReview->value,
        ]);
    }

    /**
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    public function scopeOverdueFirstResponse(Builder $query): Builder
    {
        return $query
            ->openCase()
            ->whereNull('first_responded_at')
            ->whereNull('responded_at')
            ->where(function (Builder $builder): void {
                $builder
                    ->where('first_response_due_at', '<=', now())
                    ->orWhere('created_at', '<=', now()->subHours((int) config('belzamora.inquiries.unanswered_sla_hours', 48)));
            });
    }
}

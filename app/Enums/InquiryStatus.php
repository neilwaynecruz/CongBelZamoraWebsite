<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Anonymized = 'anonymized';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::UnderReview => 'Under review',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Anonymized => 'Anonymized',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}

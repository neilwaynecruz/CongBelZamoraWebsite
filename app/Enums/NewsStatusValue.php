<?php

namespace App\Enums;

enum NewsStatusValue: string
{
    case Current = 'current';
    case Archived = 'archived';
    case StaleReview = 'stale_review';

    public function label(): string
    {
        return match ($this) {
            self::Current => 'Current advisory',
            self::Archived => 'Archived official update',
            self::StaleReview => 'Needs review before reuse',
        };
    }

    public function tone(): NewsStatusTone
    {
        return match ($this) {
            self::Current => NewsStatusTone::Current,
            self::Archived => NewsStatusTone::Archived,
            self::StaleReview => NewsStatusTone::StaleReview,
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

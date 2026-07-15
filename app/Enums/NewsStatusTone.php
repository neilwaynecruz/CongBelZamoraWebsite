<?php

namespace App\Enums;

enum NewsStatusTone: string
{
    case Current = 'current';
    case Archived = 'archived';
    case StaleReview = 'staleReview';

    public function label(): string
    {
        return match ($this) {
            self::Current => 'Current',
            self::Archived => 'Archived',
            self::StaleReview => 'Needs stale-content review',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $tone): array => [$tone->value => $tone->label()])
            ->all();
    }
}

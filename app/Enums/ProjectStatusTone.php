<?php

namespace App\Enums;

enum ProjectStatusTone: string
{
    case Completed = 'completed';
    case Ongoing = 'ongoing';
    case SourcePending = 'sourcePending';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Ongoing => 'Ongoing',
            self::SourcePending => 'Source pending',
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

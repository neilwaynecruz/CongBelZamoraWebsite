<?php

namespace App\Enums;

enum LegislativeStatusTone: string
{
    case Passed = 'passed';
    case Pending = 'pending';
    case Enacted = 'enacted';

    public function label(): string
    {
        return match ($this) {
            self::Passed => 'Passed',
            self::Pending => 'Pending',
            self::Enacted => 'Enacted',
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

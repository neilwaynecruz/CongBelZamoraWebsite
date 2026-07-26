<?php

namespace App\Enums;

enum ProjectStatusValue: string
{
    case Completed = 'completed';
    case Ongoing = 'ongoing';
    case SourcePending = 'source_pending';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed / documented',
            self::Ongoing => 'Active / ongoing service',
            self::SourcePending => 'Source review pending',
        };
    }

    public function tone(): ProjectStatusTone
    {
        return match ($this) {
            self::Completed => ProjectStatusTone::Completed,
            self::Ongoing => ProjectStatusTone::Ongoing,
            self::SourcePending => ProjectStatusTone::SourcePending,
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

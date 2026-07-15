<?php

namespace App\Enums;

enum ProgramStatus: string
{
    case Open = 'open';
    case Ongoing = 'ongoing';
    case ForAnnouncement = 'for_announcement';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Bukas / Open',
            self::Ongoing => 'Ongoing',
            self::ForAnnouncement => 'For Announcement',
            self::Closed => 'Closed',
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

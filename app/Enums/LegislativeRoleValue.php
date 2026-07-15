<?php

namespace App\Enums;

enum LegislativeRoleValue: string
{
    case PrincipalAuthor = 'principal_author';
    case CoAuthor = 'co_author';
    case Sponsor = 'sponsor';

    public function label(): string
    {
        return match ($this) {
            self::PrincipalAuthor => 'Principal Author',
            self::CoAuthor => 'Co-Author',
            self::Sponsor => 'Sponsor',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}

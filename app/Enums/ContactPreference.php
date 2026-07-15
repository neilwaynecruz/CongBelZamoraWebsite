<?php

namespace App\Enums;

enum ContactPreference: string
{
    case Email = 'email';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Sms => 'SMS / mobile',
        };
    }
}

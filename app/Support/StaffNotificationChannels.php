<?php

namespace App\Support;

final class StaffNotificationChannels
{
    /**
     * @return list<string>
     */
    public static function forStaff(): array
    {
        $channels = ['mail'];

        if ((bool) config('belzamora.staff.database_notifications', true)) {
            $channels[] = 'database';
        }

        return $channels;
    }
}

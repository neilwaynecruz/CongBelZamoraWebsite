<?php

namespace App\Listeners;

use App\Events\InquiryAssigned;
use App\Notifications\InquiryAssignedNotification;

class NotifyInquiryAssigned
{
    public function handle(InquiryAssigned $event): void
    {
        $event->assignee->notify(new InquiryAssignedNotification($event->inquiry));
    }
}

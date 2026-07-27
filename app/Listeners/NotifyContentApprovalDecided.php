<?php

namespace App\Listeners;

use App\Events\ContentApprovalDecided;
use App\Notifications\ContentApprovalDecisionNotification;

class NotifyContentApprovalDecided
{
    public function handle(ContentApprovalDecided $event): void
    {
        $submitter = $event->approval->submitter;

        if ($submitter === null) {
            return;
        }

        if ($event->actor !== null && $event->actor->is($submitter)) {
            return;
        }

        $submitter->notify(new ContentApprovalDecisionNotification(
            $event->approval,
            $event->decision,
        ));
    }
}

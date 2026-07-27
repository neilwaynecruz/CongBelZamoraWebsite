<?php

namespace App\Listeners;

use App\Enums\AdminRole;
use App\Events\ContentApprovalSubmitted;
use App\Models\User;
use App\Notifications\ContentApprovalSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class NotifyContentApprovalSubmitted
{
    public function handle(ContentApprovalSubmitted $event): void
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->whereIn('admin_role', [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
                AdminRole::ReviewerApprover->value,
            ])
            ->when(
                $event->actor !== null,
                fn ($query) => $query->whereKeyNot($event->actor->id),
            )
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new ContentApprovalSubmittedNotification($event->approval));
    }
}

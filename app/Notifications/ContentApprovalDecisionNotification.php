<?php

namespace App\Notifications;

use App\Enums\ContentApprovalStatus;
use App\Filament\Resources\ContentApprovalResource;
use App\Models\ContentApproval;
use App\Support\ReliableQueuedWork;
use App\Support\StaffNotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContentApprovalDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableQueuedWork;

    public function __construct(
        private readonly ContentApproval $approval,
        private readonly ContentApprovalStatus $decision,
    ) {
        $this->configureReliableQueue();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return StaffNotificationChannels::forStaff();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Content approval '.$this->decision->label().' #'.$this->approval->id)
            ->line('A reviewer updated your content approval item.')
            ->line('Decision: '.$this->decision->label())
            ->line('Revision: '.$this->approval->revision_number)
            ->action('Open approval', ContentApprovalResource::getUrl('view', ['record' => $this->approval]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Content approval '.$this->decision->label().' #'.$this->approval->id,
            'body' => 'Your submitted content received a review decision.',
            'content_approval_id' => $this->approval->id,
            'decision' => $this->decision->value,
            'url' => ContentApprovalResource::getUrl('view', ['record' => $this->approval]),
        ];
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('ContentApprovalDecisionNotification failed.', [
            'content_approval_id' => $this->approval->id,
            'decision' => $this->decision->value,
            'exception' => $exception?->getMessage(),
        ]);
    }
}

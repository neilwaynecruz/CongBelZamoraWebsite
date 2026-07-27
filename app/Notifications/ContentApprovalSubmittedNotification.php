<?php

namespace App\Notifications;

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

class ContentApprovalSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableQueuedWork;

    public function __construct(private readonly ContentApproval $approval)
    {
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
            ->subject('Content submitted for review #'.$this->approval->id)
            ->line('A content approval item was submitted for review.')
            ->line('Status: '.$this->approval->status->label())
            ->line('Revision: '.$this->approval->revision_number)
            ->action('Open approval queue', ContentApprovalResource::getUrl('index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Content submitted for review #'.$this->approval->id,
            'body' => 'A content item is waiting in the review queue.',
            'content_approval_id' => $this->approval->id,
            'status' => $this->approval->status->value,
            'url' => ContentApprovalResource::getUrl('view', ['record' => $this->approval]),
        ];
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('ContentApprovalSubmittedNotification failed.', [
            'content_approval_id' => $this->approval->id,
            'exception' => $exception?->getMessage(),
        ]);
    }
}

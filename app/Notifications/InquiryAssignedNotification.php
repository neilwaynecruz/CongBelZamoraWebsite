<?php

namespace App\Notifications;

use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Support\ReliableQueuedWork;
use App\Support\StaffNotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class InquiryAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableQueuedWork;

    public function __construct(private readonly Inquiry $inquiry)
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
            ->subject('Inquiry assigned: '.$this->inquiry->reference_code)
            ->line('An inquiry was assigned to you in the District Office admin panel.')
            ->line('Reference: '.$this->inquiry->reference_code)
            ->line('Category: '.$this->inquiry->inquiry_category->label())
            ->line('Status: '.$this->inquiry->status->label())
            ->line('For privacy, this email does not include citizen name, contact details, or message body.')
            ->action('Open inquiry', InquiryResource::getUrl('view', ['record' => $this->inquiry]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Inquiry assigned: '.$this->inquiry->reference_code,
            'body' => 'Category: '.$this->inquiry->inquiry_category->label().'. Open the inquiry in admin to respond.',
            'reference_code' => $this->inquiry->reference_code,
            'inquiry_id' => $this->inquiry->id,
            'url' => InquiryResource::getUrl('view', ['record' => $this->inquiry]),
        ];
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('InquiryAssignedNotification failed.', [
            'reference_code' => $this->inquiry->reference_code,
            'exception' => $exception?->getMessage(),
        ]);
    }
}

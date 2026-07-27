<?php

namespace App\Notifications;

use App\Models\Inquiry;
use App\Support\ReliableQueuedWork;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class InquiryReceivedNotification extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inquiry received: '.$this->inquiry->reference_code)
            ->greeting('Salamat po.')
            ->line('We received your inquiry and generated this reference number:')
            ->line($this->inquiry->reference_code)
            ->line('Please do not reply with IDs, medical records, income records, student records, or other sensitive documents unless the District Office provides an approved secure process.')
            ->line('Submission does not guarantee program approval, assistance, appointment confirmation, or case acceptance.');
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('InquiryReceivedNotification failed.', [
            'reference_code' => $this->inquiry->reference_code,
            'exception' => $exception?->getMessage(),
        ]);
    }
}

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

class OverdueInquiryReminderNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableQueuedWork;

    public function __construct(private readonly Inquiry $inquiry)
    {
        $this->configureReliableQueue();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return StaffNotificationChannels::forStaff();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Overdue inquiry needs first response: '.$this->inquiry->reference_code)
            ->line('An open inquiry has passed its first-response target.')
            ->line('Reference: '.$this->inquiry->reference_code)
            ->line('Category: '.$this->inquiry->inquiry_category->label())
            ->line('Priority: '.$this->inquiry->priority->label())
            ->line('Office unit: '.($this->inquiry->office_unit ?? 'Unassigned office unit'))
            ->line('For privacy, this email does not include citizen name, contact details, or message body.')
            ->action('Open inquiry', InquiryResource::getUrl('view', ['record' => $this->inquiry]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Overdue inquiry: '.$this->inquiry->reference_code,
            'body' => 'First response is overdue. Category: '.$this->inquiry->inquiry_category->label().'.',
            'reference_code' => $this->inquiry->reference_code,
            'inquiry_id' => $this->inquiry->id,
            'priority' => $this->inquiry->priority->value,
            'url' => InquiryResource::getUrl('view', ['record' => $this->inquiry]),
        ];
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('OverdueInquiryReminderNotification failed.', [
            'reference_code' => $this->inquiry->reference_code,
            'exception' => $exception?->getMessage(),
        ]);
    }
}

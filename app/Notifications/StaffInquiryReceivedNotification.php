<?php

namespace App\Notifications;

use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffInquiryReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Inquiry $inquiry) {}

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
            ->subject('New public inquiry: '.$this->inquiry->reference_code)
            ->line('A new public inquiry was submitted through the District Office website.')
            ->line('Reference: '.$this->inquiry->reference_code)
            ->line('Category: '.$this->inquiry->inquiry_category->label())
            ->line('Status: '.$this->inquiry->status->label())
            ->line('For privacy, this email does not include the citizen name, contact details, or message body.')
            ->action('Open inquiry in admin', InquiryResource::getUrl('view', ['record' => $this->inquiry]));
    }
}

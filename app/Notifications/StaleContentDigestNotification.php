<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaleContentDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, int>  $counts
     */
    public function __construct(private readonly array $counts) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = array_sum($this->counts);

        return (new MailMessage)
            ->subject('Bel Zamora website content review needed')
            ->line("The scheduled content freshness check found {$total} record(s) requiring review.")
            ->line('Please review overdue public content, source records, and pending approvals in the admin panel.')
            ->action('Open Admin Panel', url('/admin'));
    }

    /**
     * @return array<string, int>
     */
    public function toArray(object $notifiable): array
    {
        return $this->counts;
    }
}

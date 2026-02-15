<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLinkNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $url
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Login Link')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Click the button below to log in.')
            ->action('Log In', $this->url)
            ->line('This link expires in 15 minutes.')
            ->line('If you did not request this link, no action is needed.');
    }
}

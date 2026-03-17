<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvanceRequestNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $url;

    public function __construct(string $message, string $url = '#')
    {
        $this->message = $message;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}


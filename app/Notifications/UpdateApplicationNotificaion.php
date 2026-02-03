<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpdateApplicationNotificaion extends Notification
{
    use Queueable;

    protected $application;
    protected $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Application $application, $message)
    {
        $this->application = $application;
        $this->message = $message;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'app_id' => $this->application->app_id,
            'customer_name' => $this->application->customer_name,
            'message' => $this->mas,
            'url' => url('/application/view/' . $this->application->id),
        ];
    }
}

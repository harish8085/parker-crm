<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CheckerRejectedApplicationNotification extends Notification
{
    use Queueable;

    protected $application;
    protected $reason;

    public function __construct(Application $application, $reason)
    {
        $this->application = $application;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'app_id' => $this->application->app_id,
            'customer_name' => $this->application->customer_name,
            'message' => 'Application ' . $this->application->app_id . ' has been rejected by the Checker. Reason: ' . $this->reason . '. Please review.',
            'url' => url('/application/update/' . $this->application->id),
        ];
    }
}

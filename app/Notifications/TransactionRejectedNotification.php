<?php

namespace App\Notifications;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionRejectedNotification extends Notification
{
    use Queueable;

    protected $transaction;
    protected $channelUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transaction $transaction, User $channelUser)
    {
        $this->transaction = $transaction;
        $this->channelUser = $channelUser;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $channelName = $this->channelUser->first_name . ' ' . $this->channelUser->last_name;

        return [
            'transaction_id' => $this->transaction->id,
            'message' => 'Channel "' . $channelName . '" has rejected transaction #' . $this->transaction->id . '. Reason: ' . $this->transaction->rejection_reason,
            'url' => url('/transactions/view/' . $this->transaction->id),
        ];
    }
}

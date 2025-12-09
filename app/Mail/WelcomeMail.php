<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = $this->user;
        $mail = $this->subject('Welcome to ' . env('APP_NAME'))
                    ->view('emails.maker-checker-welcome',compact('user') );
        return $mail;
    }
}

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
    public $verificationLink;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        // Generate encrypted verification link
        $encryptedData = Crypt::encryptString($user->id . '|' . $user->email);
        $this->verificationLink = url('/verify-email?token=' . urlencode($encryptedData));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject('Welcome to ' . env('APP_NAME') . ' - Terms & Conditions')
                    ->view('emails.welcome');

        // Attach terms and conditions file if it exists
        $termsPath = storage_path('app/public/terms-and-conditions.pdf');
        if (!file_exists($termsPath)) {
            // Try alternative locations
            $termsPath = public_path('terms-and-conditions.pdf');
        }
        
        if (file_exists($termsPath)) {
            $mail->attach($termsPath, [
                'as' => 'Terms-and-Conditions.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}

<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountActivation extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
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
        return $this->subject('Account Activation Required')
            ->view('emails.account-activation')
            ->with([
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'activationLink' => route('send.technician.activation', ['token' => md5($this->user->email)]),
            ]);
    }
}

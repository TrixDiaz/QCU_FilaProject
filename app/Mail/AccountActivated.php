<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountActivated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Your Account Has Been Activated')
            ->markdown('emails.account-activated')
            ->with([
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
            ]);
    }
}

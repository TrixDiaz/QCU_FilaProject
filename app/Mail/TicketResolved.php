<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Ticket;
use App\Models\User;

class TicketResolved extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $resolvedByUser;

    public function __construct(Ticket $ticket, User $resolvedByUser)
    {
        $this->ticket = $ticket;
        $this->resolvedByUser = $resolvedByUser;
    }

    public function build()
    {
        return $this->subject('Ticket #' . $this->ticket->ticket_number . ' Has Been Resolved')
            ->markdown('emails.tickets.resolved');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Ticket;
use App\Models\User;

class TicketInProgress extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $assignedUser;

    public function __construct(Ticket $ticket, User $assignedUser)
    {
        $this->ticket = $ticket;
        $this->assignedUser = $assignedUser;
    }

    public function build()
    {
        return $this->subject('Ticket #' . $this->ticket->ticket_number . ' Marked as In Progress')
            ->markdown('emails.tickets.in-progress');
    }
}

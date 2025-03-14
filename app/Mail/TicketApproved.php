<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Ticket Has Been Approved')
            ->markdown('emails.tickets.approved')
            ->with([
                'ticketTitle' => $this->data['ticketTitle'],
                'ticketType' => $this->data['ticketType'],
                'ticketOption' => $this->data['ticketOption'],
                'userName' => $this->data['userName'],
            ]);
    }
}

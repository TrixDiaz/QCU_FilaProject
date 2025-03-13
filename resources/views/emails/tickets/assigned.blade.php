@component('mail::message')
    # Ticket Assigned

    A new ticket has been assigned to you.

    **Ticket Details:**
    - **Ticket Number:** {{ $ticket->ticket_number }}
    - **Title:** {{ $ticket->title }}
    - **Type:** {{ ucfirst($ticket->ticket_type) }}
    - **Status:** {{ ucfirst($ticket->status) }}


    Thank you,
    {{ config('app.name') }}
@endcomponent

@component('mail::message')
    # Ticket Created

    Your ticket has been successfully created.

    **Ticket Details:**
    - **Ticket Number:** {{ $ticket->ticket_number }}
    - **Title:** {{ $ticket->title }}
    - **Type:** {{ ucfirst($ticket->ticket_type) }}
    - **Status:** {{ ucfirst($ticket->ticket_status) }}

    Thank you,
    {{ config('app.name') }}
@endcomponent

@component('mail::message')
    # Ticket Status Update

    Ticket #{{ $ticket->ticket_number }}: {{ $ticket->title }} has been marked as **In Progress**.

    **Technician:** {{ $assignedUser->name }}
    **Updated at:** {{ now()->format('F j, Y, g:i a') }}

    Thank you,
    {{ config('app.name') }}
@endcomponent

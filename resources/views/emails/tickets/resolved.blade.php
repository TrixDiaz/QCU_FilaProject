@component('mail::message')
    # Ticket Resolved

    Your ticket (#{{ $ticket->ticket_number }}) has been resolved by {{ $resolvedByUser->name }}.

    @if ($ticket->updated_at)
        Resolved on: {{ $ticket->updated_at->format('Y-m-d H:i:s') }}
    @else
        Not available
    @endif


    Thank you,
    {{ config('app.name') }}
@endcomponent

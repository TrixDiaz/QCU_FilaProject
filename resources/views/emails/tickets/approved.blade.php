@component('mail::message')
    # Ticket Approved

    Hello {{ $userName }},

    Your ticket "{{ $ticketTitle }}" has been approved.

    **Details:**
    - Type: {{ ucfirst($ticketType) }}
    - Category: {{ ucfirst($ticketOption) }}

    Thank you for using our system.

    @component('mail::button', ['url' => config('app.url')])
        Go to Dashboard
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = ['ticket_number, asset_id, created_by, assigned_to, section_id, title, description,
    ticket_type, priority, due_date, date_finished, attachment, status, created_at, updated_at'];

    protected $cast = [ 
        'attachment' => 'array'
    ];

    public function Asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

     public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }


    public function section() : BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

//     protected static function boot()
// {
//     parent::boot();

//     static::creating(function ($ticket) {
//         // Set created_by if empty
//         if (empty($ticket->created_by) && auth()->check()) {
//             $ticket->created_by = auth()->id(); // Assuming created_by is a user ID
//         }

//         // Determine prefix based on ticket type
//         $prefix = $ticket->ticket_type === 'request' ? 'REQ-' : 'INC-';
        
//         // Get the last ticket number of this type
//         $lastTicket = static::where('ticket_type', $ticket->ticket_type)
//                            ->orderBy('id', 'desc')
//                            ->first();
        
//         // Generate new ticket number
//         if (!$lastTicket) {
//             $newNumber = $prefix . str_pad(1, 6, '0', STR_PAD_LEFT);
//         } else {
//             // Extract the numeric part after the prefix (e.g., after 'REQ-' or 'INC-')
//             $lastNumber = intval(substr($lastTicket->ticket_number, 4));
//             $newNumber = $prefix . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
//         }
        
//         $ticket->ticket_number = $newNumber;
//     });
//     }
}


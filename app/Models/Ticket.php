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

    protected $guarded = [];

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {

            // Get the last ticket number
            $lastTicket = static::orderBy('id', 'desc')->first();
            
            // Generate new ticket number
            if (!$lastTicket) {
                $newNumber = 'TKT-' . str_pad(1, 6, '0', STR_PAD_LEFT);
            } else {
                $lastNumber = intval(substr($lastTicket->ticket_number, 4));
                $newNumber = 'TKT-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            }
            
            $ticket->ticket_number = $newNumber;
        });
    }
}


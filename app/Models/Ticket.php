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

    protected $fillable = [
        'ticket_number', 'asset_id', 'created_by', 'assigned_to', 'section_id',
        'title', 'description', 'ticket_type', 'option', 'priority', 'due_date',
        'date_finished', 'attachment', 'status', 'created_at', 'updated_at'
    ];


    protected $cast = [
          'attachment' => 'array',
//        'due_date' => 'datetime',
//        'date_finished' => 'datetime',
    ];

    public function asset(): BelongsTo
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

}


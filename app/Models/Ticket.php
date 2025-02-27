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
        'title', 'description', 'ticket_type', 'option', 'priority', 'starts_at',
        'ends_at', 'attachment', 'status', 'created_at', 'updated_at', 'subject'
    ];


    protected $cast = [
          'attachment' => 'array',
          'starts_at' => 'datetime',
          'ends_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($ticket) {
            if ($ticket->ticket_type === 'request') {
                Approval::create([
                    'asset_id' => $ticket->asset_id,
                    'professor_id' => $ticket->assigned_to,
                    'section_id' => $ticket->section_id,
                    'subject_id' => null,
                    'title' => $ticket->title,
                    'color' => 'blue',
                    'starts_at' => now(),
                    'ends_at' => $ticket->due_date ?? now()->addDays(7),
                ]);
            }
        });
    }

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

    public function subject() : BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function setAttachmentAttribute($value)
    {
        $this->attributes['attachment'] = is_array($value) ? json_encode($value) : $value;
    }

}


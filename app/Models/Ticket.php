<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'terminal_number',
        'title',
        'description',
        'priority',
        'type',
        'subtype',
        'asset_id',
        'assigned_to',
        'created_by',
        'ticket_type',
        'ticket_status',
        'classroom_id',
        'section_id',
        'start_time',
        'end_time',
        'terminal',
    ];

    protected $casts = [
        'attachments' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'ticket_status' => 'string',
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

    /**
     * Get the technician assigned to this ticket
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function setAttachmentsAttribute($value)
    {
        $this->attributes['attachments'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getAttachmentsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function approval()
    {
        return $this->hasOne(Approval::class, 'ticket_number', 'ticket_number');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($ticket) {
            if ($ticket->type === 'classroom_request') {
                Approval::create([
                    'ticket_id' => $ticket->id,
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_at' => null
                ]);
            }
        });
    }
}

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
            // Changed condition to check for 'request' or 'classroom' type in ticket_type
            if (in_array($ticket->ticket_type, ['request', 'classroom'])) {
                try {
                    $approval = Approval::create([
                        'ticket_number' => $ticket->ticket_number,
                        'title' => $ticket->title,
                        'description' => $ticket->description,
                        'asset_id' => $ticket->asset_id,
                        'section_id' => $ticket->section_id,
                        'professor_id' => $ticket->created_by,
                        'subject_id' => $ticket->subject_id ?? null,
                        'option' => $ticket->type ?? $ticket->ticket_type, // Use type if available, fallback to ticket_type
                        'starts_at' => $ticket->start_time,
                        'ends_at' => $ticket->end_time,
                        'status' => 'pending',
                        'classroom_id' => $ticket->classroom_id
                    ]);
                    
                    // Update ticket status to in progress
                    $ticket->update(['ticket_status' => 'in progress']);
                    
                    \Log::info('Approval created successfully', [
                        'ticket_number' => $ticket->ticket_number,
                        'approval_id' => $approval->id,
                        'ticket_type' => $ticket->ticket_type,
                        'type' => $ticket->type ?? 'not set'
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create approval', [
                        'ticket_number' => $ticket->ticket_number,
                        'error' => $e->getMessage(),
                        'ticket_type' => $ticket->ticket_type
                    ]);
                }
            }
        });

        static::updating(function ($ticket) {
            // Check for both request and classroom types
            if ($ticket->isDirty('ticket_type') && 
                ($ticket->ticket_type === 'request' || $ticket->ticket_type === 'classroom')) {
                try {
                    Approval::firstOrCreate(
                        ['ticket_number' => $ticket->ticket_number],
                        [
                            'title' => $ticket->title,
                            'description' => $ticket->description,
                            'asset_id' => $ticket->asset_id,
                            'section_id' => $ticket->section_id,
                            'professor_id' => $ticket->created_by,
                            'subject_id' => $ticket->subject_id,
                            'option' => $ticket->ticket_type,
                            'starts_at' => $ticket->start_time,
                            'ends_at' => $ticket->end_time,
                            'status' => 'pending',
                            'classroom_id' => $ticket->classroom_id
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to create/update approval', [
                        'ticket_number' => $ticket->ticket_number,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    protected $fillable = [
        'ticket_id',
        'ticket_number',
        'asset_id',
        'professor_id',
        'section_id',
        'subject_id',
        'option',
        'title',
        'color',
        'starts_at',
        'ends_at',
        'status',
        'attachment',
        'description',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($approval) {
            // If we have a ticket_id but no dates set, get them from the ticket
            if ($approval->ticket_id && (!$approval->starts_at || !$approval->ends_at)) {
                $ticket = Ticket::find($approval->ticket_id);
                if ($ticket) {
                    $approval->starts_at = $approval->starts_at ?? $ticket->starts_at;
                    $approval->ends_at = $approval->ends_at ?? $ticket->ends_at;

                    // Also copy option field if needed
                    if (!$approval->option && $ticket->option) {
                        $approval->option = $ticket->option;
                    }
                }
            }
        });

        static::created(function (Approval $approval) {
            if ($approval->ticket) {
                $approval->ticket->update(['status' => 'in progress']);
            }
        });
    }

   
    // In Approval.php model
public function ticket()
{
    return $this->belongsTo(Ticket::class, 'ticket_id');
}

public function asset()
{
    return $this->belongsTo(Asset::class);
}

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }




    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }


   

    public function setAttachmentsAttribute($value)
    {
        $this->attributes['attachments'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getAttachmentsAttribute($value)
    {
        return json_decode($value, true);
    }
}






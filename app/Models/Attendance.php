<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'terminal_id',
        'student_full_name',
        'student_email',
        'student_number',
        'remarks'
    ];

    // Relationships
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(AssetGroup::class, 'code');
    }
}

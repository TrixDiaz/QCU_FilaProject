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
        'professor_id',
        'section_id',
        'terminal_code',
        'student_full_name',
        'student_email',
        'student_number',
        'year_section',
        'remarks'
    ];

    // Relationships
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(AssetGroup::class, 'code');
    }
}

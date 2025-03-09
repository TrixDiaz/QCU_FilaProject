<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'terminal_number',
        'student_full_name',
        'student_email',
        'student_number',
        'peripherals',
        'remarks',
    ];

    protected $casts = [
        'fail_peripherals' => 'array',
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

    public function scopeForProfessor(Builder $query, $userId)
    {
        return $query->whereHas('subject', function ($query) use ($userId) {
            $query->where('professor_id', $userId);
        });
    }

    // Add these scopes
    public function scopeForSchoolYear(Builder $query, $schoolYear)
    {
        return $query->whereHas('subject', function ($query) use ($schoolYear) {
            $query->where('school_year', $schoolYear);
        });
    }

    public function scopeForSemester(Builder $query, $semester)
    {
        return $query->whereHas('subject', function ($query) use ($semester) {
            $query->where('semester', $semester);
        });
    }
}

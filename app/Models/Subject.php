<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectFactory> */
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'section_id',
        'professor_id',
        'name',
        'subject_code',
        'subject_units',
        'day',
        'lab_time',
        'lecture_time',
        'status',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'subject_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id')->with('classroom.building');
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function classroom()
    {
        return $this->hasOneThrough(
            Classroom::class,
            Section::class,
            'id', // Foreign key on sections table
            'id', // Foreign key on classrooms table
            'section_id', // Local key on subjects table
            'classroom_id' // Local key on sections table
        );
    }
}

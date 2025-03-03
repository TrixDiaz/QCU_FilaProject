<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    /** @use HasFactory<\Database\Factories\SectionFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'classroom_id',
        'name',
        'slug',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id')->with('building');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'section_id');
    }

    public function subject(): HasMany
    {
        return $this->hasMany(subject::class, 'section_id');
    }

}

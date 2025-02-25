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

    public function getRouteKeyName()
    {
        return 'slug';
    }
    protected $fillable = [
        'classroom_id',
        'name',
        'slug',
        'is_active'
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class)->with('building');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'section_id');
    }

}

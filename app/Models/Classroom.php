<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{

    /** @use HasFactory<\Database\Factories\ClassroomFactory> */
    use HasFactory;
    protected $guarded = [];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class)->where('is_active', true);
    }

    public function sections(): HasMany
    {
        return $this->HasMany(Section::class)->where('is_active', true);
    }
}

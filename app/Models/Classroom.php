<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{

    /** @use HasFactory<\Database\Factories\ClassroomFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'building_id',
        'name',
        'slug',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function sections(): HasMany
    {
        return $this->HasMany(Section::class);
    }

    public function asset(): HasMany
    {
        return $this->HasMany(Asset::class);
    }


}

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
        'name',
        'capacity',
        'slug',
        'floor',
        'building_id',
        'floor',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function sections(): HasMany
    {
        return $this->HasMany(Section::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function assetGroups(): HasMany
    {
        return $this->hasMany(AssetGroup::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'classroom_id');
    }
}

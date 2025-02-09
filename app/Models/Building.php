<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    /** @use HasFactory<\Database\Factories\BuildingFactory> */
    use HasFactory;
    use SoftDeletes;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $fillable = [
        'name',
        'slug',
        'is_active'
    ];

    public function classrooms(): HasMany
    {
        return $this->HasMany(Classroom::class);
    }

    public function getAssetsCountAttribute()
    {
        return $this->classrooms->sum(function ($classroom) {
            return $classroom->assets()->count();
        });
    }
}

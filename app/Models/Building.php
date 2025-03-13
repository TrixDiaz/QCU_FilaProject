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

    protected $table = "buildings";
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'created_at',
        'updated_at',
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

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

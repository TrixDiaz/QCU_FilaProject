<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    public $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'created_at',
        'updated_at',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(\App\Models\Asset::class);
    }
}

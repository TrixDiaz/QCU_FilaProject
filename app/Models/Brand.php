<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;

    public $table = 'brands';
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

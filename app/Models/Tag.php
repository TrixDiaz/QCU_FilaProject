<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    public $table = 'tags';
    protected $fillable = [
        'name',
        'is_active',
        'slug',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Asset::class, 'asset_tags', 'asset_tag_id', 'asset_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;
    use SoftDeletes;

    public $table = 'tags';
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Asset::class, 'asset_tags', 'asset_tag_id', 'asset_id');
    }

}

<?php

namespace App\Models;

use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asset extends Model
{
    use HasFactory;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public $table = 'assets';
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'serial_number',
        'asset_code',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'status' => AssetStatus::class,
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function assetTags(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Tag::class, 'asset_tags', 'asset_id', 'asset_tag_id');
    }

}

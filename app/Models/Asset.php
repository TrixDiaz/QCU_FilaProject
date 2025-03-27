<?php

namespace App\Models;

use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Asset extends Model
{
    use HasFactory;


    public $table = 'assets';
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'serial_number',
        'asset_code',
        'expiry_date',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assetTags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'asset_tags', 'asset_id', 'asset_tag_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'asset_id');
    }

    public function assetGroups(): HasMany
    {
        return $this->hasMany(AssetGroup::class, 'asset_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->assetTags();
    }
}

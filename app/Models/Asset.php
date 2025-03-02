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
        'slug',
        'serial_number',
        'asset_code',
        'expiry_date',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
//        'status' => AssetStatus::class,
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

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'asset_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function assetGroup(): HasOne
    {
        return $this->hasOne(\App\Models\AssetGroup::class, 'asset_id');
    }




}

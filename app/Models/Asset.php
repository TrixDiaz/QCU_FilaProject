<?php

namespace App\Models;

use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $guarded = [];

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
    public function tag(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tag::class);
    }

}

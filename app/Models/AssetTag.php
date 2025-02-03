<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTag extends Model
{
    use HasFactory;

    public $table = 'asset_tags';
    protected $fillable = [
        'asset_id',
        'asset_tag_id'
    ];

    public function tags(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tag::class);
    }
}

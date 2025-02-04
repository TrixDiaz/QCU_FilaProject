<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalAsset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetTagFactory> */
    use HasFactory;

    public $table = 'terminal_assets_group';

    protected $fillable = [
        'asset_id',
        'classroom_id',
        'name',
        'slug',
        'terminal_code',
        'status'
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Classroom::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Asset::class);
    }
}

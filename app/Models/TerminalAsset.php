<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalAsset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetTagFactory> */
    use HasFactory;

    public $table = 'terminal_assets_group';

    protected $fillable = [
        'asset_id',
        'classroom_id',
    ];
}

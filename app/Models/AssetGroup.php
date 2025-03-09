<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetGroup extends Model
{
    use HasFactory;

    public $table = 'assets_group';

    protected $fillable = [
        'asset_id',
        'classroom_id',
        'name',
        'code',
        'status',
        'created_at',
        'updated_at',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function classroomAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }

    public function assets(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }
}

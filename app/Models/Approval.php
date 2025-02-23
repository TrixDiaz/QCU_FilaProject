<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    /** @use HasFactory<\Database\Factories\ApprovalFactory> */
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'professor_id',
        'section_id',
        'subject_id',
        'title',
        'color',
        'starts_at',
        'ends_at',
    ];
}

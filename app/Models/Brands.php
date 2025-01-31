<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brands extends Model
{
    /** @use HasFactory<\Database\Factories\BrandsFactory> */
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
}

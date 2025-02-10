<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
      'professor_id',
      'section_id',
      'subject_id',
      'title',
      'color',
      'starts_at',
      'ends_at',
    ];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'tutor_id',
        'group_id',
        'start_time',
        'end_time',
        'marked_by',
    ];
}

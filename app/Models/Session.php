<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

     // Pour le tuteur: récupérer ses séances
     public function module(): BelongsTo
     {
         return $this->belongsTo(Module::class);
     }
}

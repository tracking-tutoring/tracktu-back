<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weeks_duration',
        'user_id',
        // 'hours',
        // 'hour_completed',
        // 'hour_not_completed',
    ];

    public function tutors()
    {
        return $this->belongsToMany(User::class, 'module_tutor')->withPivot('assigned_by');
    }
    
}

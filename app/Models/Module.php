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
        'description',
        'picture',
        'user_id',
    ];

    // Générer l'URL complète de l'image
    public function getImageUrl()
    {
        if ($this->picture) {
            return asset('storage/' . $this->picture);
        }

        return null; // Si aucune image n'est définie
    }

    public function tutors()
    {
        return $this->belongsToMany(User::class, 'module_tutor', 'module_id', 'tutor_id');
    }

    public function groups() {
        return $this->belongsToMany(Group::class, 'affectations')->withPivot('tutor_id', 'assigned_by');
    }
    
}

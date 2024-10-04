<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    use HasFactory;

    protected $fillable = ['tuteur_id', 'module_id', 'groupe_id'];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}

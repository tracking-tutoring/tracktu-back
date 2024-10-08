<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        // 'registration_number',
        'email',
        'phone_number',
        'role',
        'email_verified_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Pour le tuteur: récupérer ses séances
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'tutor_id');
    }

    // Pour le tuteur: récupérer les groupes associés
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'affectations', 'tutor_id', 'group_id')->withPivot('module_id');
    }

    // Pour le tuteur: récupérer ses modules associés
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_tutor', 'tutor_id', 'module_id');
    }

    // public function tutors()
    // {
    //     return $this->belongsToMany(User::class, 'module_tutor', 'module_id', 'tutor_id')->withPivot('assigned_by');
    // }
}

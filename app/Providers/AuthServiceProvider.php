<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('create', function (User $user) {
            return $user->role == 'tracking'
            ? Response::allow()
            : Response::deny('Vous devez être membre de l\'équipe de tracking.');
        });

        Gate::define('update', function (User $user) {
            return $user->role == 'tracking'
            ? Response::allow() 
            : Response::deny('Vous n\'êtes pas autorisé à mettre à jour.');
        });

        Gate::define('delete', function (User $user) {
            return $user->role == 'tracking'  
            ? Response::allow() 
            : Response::deny('Vous n\'êtes pas autorisé à supprimer.');
        });

        // Gate::define('show', function (User $user, $model) {
        //     return $user->id == $model->user_id  
        //     ? Response::allow() 
        //     : Response::deny('Vous n\'êtes pas autorisé à voir.');
        // });
    }
}

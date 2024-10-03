<?php

use App\Http\Controllers\Api\V1\AffectationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\StatisticController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// routes pour l'équipe de tracking
Route::middleware(['auth:sanctum', 'checkRole:tracking', 'tokenExp'])->prefix('tracking')->group(function() {
    Route::controller(ModuleController::class)->group(function() {
          Route::get('modules', 'index');
          Route::post('modules', 'store');
          Route::get('modules/{module}', 'show');
          Route::put('modules/{module}', 'update');
          Route::delete('modules/{module}', 'destroy');
    });

    Route::controller(SessionController::class)->group(function() {
        Route::get('sessions', 'index');
        Route::post('sessions', 'store');
        Route::get('sessions/{session}', 'show');
        Route::get('sessions/tutor/{tutor}/{module?}', 'showtutorSessions');
        // Route::put('sessions/{session}', 'update'); // * Pour l'instant on a pas besoin de mettre à jour une session
        Route::put('sessions/mark/{session}', 'markSession');
    });

    Route::controller(AffectationController::class)->group(function() {
        Route::post('affectations/tutor', 'assignTutor');
        Route::delete('affectations/tutor', 'deleteTutorAssignment');
        Route::post('affectations/groups', 'store');
        Route::delete('affectations/groups', 'destroy');
        
    });

    Route::controller(UserController::class)->group(function() {
        Route::get('users/{userRole}', 'getUsers');
        Route::get('users/{userRole}/{userid}', 'getUser');
        Route::post('users', 'createUser');
        Route::delete('users/{userId}', 'deleteUser');
    });

    Route::controller(StatisticController::class)->group(function() {
        Route::get('statistics/for-cards', 'getStatisticsForCards');
        Route::get('statistics/hours-done/{module}/{id}', 'getHoursDone');
        Route::get('statistics/hours-not-done/{module}/{id}', 'getHoursNotDone');
    });

    Route::controller(GroupController::class)->group(function() {
        Route::get('groups', 'index');
        Route::post('groups', 'store');
        Route::get('groups/{group}', 'show');
        Route::put('groups/{group}', 'update');
        Route::delete('groups/{group}', 'destroy');
    });

    Route::controller(ProfileController::class)->group(function() {
        Route::get('profile', 'index');
        Route::put('profile', 'update');
        Route::put('profile/update-password', 'updatePassword');
    });
    
});

// route pour l'admin
Route::middleware(['auth:sanctum', 'checkRole:tutor', 'tokenExp'])->prefix('tutor')->group(function() {
    Route::controller(ModuleController::class)->group(function() {
        Route::get('modules/my-modules', 'getTutorModules'); 
    });

    Route::controller(SessionController::class)->group(function() {
        Route::get('sessions/my-sessions/{moduleId?}', 'getTutorSessions'); 
    });

    Route::controller(GroupController::class)->group(function() {
        Route::get('groups/my-groups/{moduleId?}', 'getTutorgroups'); 
    });
    
});


Route::post('register', [RegisterController::class, 'store']);
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::controller(ForgotPasswordController::class)->group(function() {
     Route::post('forgot-password', 'forgotPassword');
     Route::post('check-code', 'checkCode');
     Route::post('change-password', 'changePassword');
});
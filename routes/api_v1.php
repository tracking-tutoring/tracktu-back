<?php

use App\Http\Controllers\Api\V1\AffectationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\SessionController;
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

Route::middleware(['auth:sanctum', 'checkRole:tracking'])->prefix('tracking')->group(function() {
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
        Route::put('sessions/{session}', 'update');
        Route::put('sessions/mark/{session}', 'markSession');
    });

    Route::controller(AffectationController::class)->group(function() {
        Route::post('affectations/tutor', 'assignTutor');
        Route::delete('affectations/tutor', 'deleteTutorAssignment');
        Route::post('affectations/groups', 'store');
        Route::delete('affectations/groups', 'destroy');
        
    });

    Route::controller(UserController::class)->group(function() {
        Route::get('users/tutor', 'getTutors');
        Route::get('users/tutor/{tutor}', 'getTutor');
        Route::post('users/tutor', 'createTutor');
        Route::delete('users/tutor/{tutor}', 'deleteTutor');
    });

  
    
});


// Route::middleware(['auth:sanctum', 'checkRole:tutor'])->prefix('tutor')->group(function (Request $request) {
    
// });


Route::post('register', [RegisterController::class, 'store']);
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::controller(ForgotPasswordController::class)->group(function() {
     Route::post('forgot-password', 'forgotPassword');
     Route::post('check-code', 'checkCode');
     Route::post('change-password', 'changePassword');
});
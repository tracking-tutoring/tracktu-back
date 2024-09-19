<?php

use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
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

// Route::middleware(['auth:sanctum', 'checkRole:admin'])->prefix('tracking')->group(function (Request $request) {
    
// });


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
<?php

use App\Http\Controllers\IzinController;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Logout;
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

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[UserController::class,'logout']);
    Route::get('users',[UserController::class,'users']);
    Route::patch('password',[UserController::class,'changePassword']);
    Route::post('reset_password',[UserController::class,'resetPassword']);

    Route::post('add_verifikator',[UserController::class,'addVerifikator']);
    Route::put('change_role',[UserController::class,'changeRole']);
    Route::post('verify_user',[UserController::class,'verifyUser']);

    Route::post('izin',[IzinController::class,'create']);
    Route::get('izin',[IzinController::class,'index']);
    Route::put('izin',[IzinController::class,'update']);
    Route::delete('izin',[IzinController::class,'delete']);
    Route::put('batal_izin',[IzinController::class,'batal']);
    Route::post('verify_izin',[IzinController::class,'verifyIzin']);
});



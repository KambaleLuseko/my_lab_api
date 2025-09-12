<?php

use App\Http\Controllers\RoomManagerController;
use App\Http\Controllers\SallesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\DashboardController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/users/login', [UserController::class, 'login']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::resource('salles', SallesController::class);
Route::apiResource('room-manager', RoomManagerController::class);

Route::resource('services', ServicesController::class);

// Routes de gestion de permission selon le rôle des utilisateurs
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
});

Route::middleware(['role:etudiant'])->group(function () {
    Route::get('/etudiant', [DashboardController::class, 'index'])->name('etudiant.dashboard');
});

Route::middleware(['role:enseignant'])->group(function () {
    Route::get('/enseignant', [DashboardController::class, 'index'])->name('enseignant.dashboard');
});



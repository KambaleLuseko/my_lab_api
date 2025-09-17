<?php

use App\Http\Controllers\RoomManagerController;
use App\Http\Controllers\SallesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoomAccessController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\DashboardController;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes utilisateurs
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/users/login', [UserController::class, 'login']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Salles & gestion
Route::resource('salles', SallesController::class);
Route::apiResource('room-manager', RoomManagerController::class);
Route::apiResource('user-room-access', UserRoomAccessController::class);
Route::post('user-room-access/approve', [UserRoomAccessController::class, 'updateStatus']);
Route::post('user-room-access/reject', [UserRoomAccessController::class, 'rejectDemands']);
Route::post('user-room-access/cancel', [UserRoomAccessController::class, 'cancelDemands']);

// Services
Route::resource('services', ServicesController::class);

// Gestion rôles & permissions
Route::middleware(['auth:sanctum', 'permission:manage_users'])->group(function () {
    // Voir tous les rôles
    Route::get('/roles', function () {
        return Role::with('permissions')->get();
    });

    // Voir toutes les permissions
    Route::get('/permissions', function () {
        return Permission::all();
    });

    // Assigner un rôle
    Route::post('/users/{user}/assign-role', function (Request $request, User $user) {
        $role = Role::findOrFail($request->role_id);
        $user->roles()->syncWithoutDetaching([$role->id]);
        return response()->json([
            'message' => 'Rôle attribué avec succès',
            'user' => $user->load('roles')
        ]);
    });

    // Retirer un rôle
    Route::post('/users/{user}/remove-role', function (Request $request, User $user) {
        $role = Role::findOrFail($request->role_id);
        $user->roles()->detach($role->id);
        return response()->json([
            'message' => 'Rôle retiré avec succès',
            'user' => $user->load('roles')
        ]);
    });

    // Voir permissions d’un utilisateur
    Route::get('/users/{user}/permissions', function (User $user) {
        $permissions = $user->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');

        return response()->json([
            'user' => $user->only('id', 'name', 'email'),
            'permissions' => $permissions->values()
        ]);
    });
});

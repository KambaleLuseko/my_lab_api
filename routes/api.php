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



    // Routes publiques (login, etc.)
    // Route::post('/users/login', [UserController::class, 'login']);

    Route::post('/login', function(Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Générer un token sanctum
    $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user->only('id','name','email'),
            'token' => $token
        ]);
    });


    // Routes accessibles uniquement aux admins
    Route::middleware(['auth:sanctum', 'permission:manage_users'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::patch('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });

      // Routes pour gérer les salles (admin seulement)
    Route::middleware(['auth:sanctum', 'permission:manage_rooms'])->group(function () {
        Route::resource('salles', SallesController::class);
        Route::apiResource('room-manager', RoomManagerController::class);
    });



    Route::middleware(['auth:sanctum', 'permission:manage_users'])->group(function () {
                // 1. Voir tous les rôles
                Route::get('/roles', function () {
                    return Role::with('permissions')->get();
                });

                // 2. Voir toutes les permissions
                Route::get('/permissions', function () {
                    return Permission::all();
                });

                // 3. Assigner un rôle à un utilisateur
                Route::post('/users/{user}/assign-role', function (Request $request, User $user) {
                    $role = Role::findOrFail($request->role_id);
                    $user->roles()->syncWithoutDetaching([$role->id]);

                    return response()->json([
                        'message' => 'Rôle attribué avec succès',
                        'user' => $user->load('roles')
                    ]);
                });

                // 4. Retirer un rôle d’un utilisateur
                Route::post('/users/{user}/remove-role', function (Request $request, User $user) {
                    $role = Role::findOrFail($request->role_id);
                    $user->roles()->detach($role->id);

                    return response()->json([
                        'message' => 'Rôle retiré avec succès',
                        'user' => $user->load('roles')
                    ]);
                });

                // 5. Voir les permissions d’un utilisateur
                Route::get('/users/{user}/permissions', function (User $user) {
                    $permissions = $user->roles()->with('permissions')->get()
                        ->pluck('permissions')
                        ->flatten()
                        ->unique('id');

                        return response()->json([
                            'user' => $user->only('id','name','email'),
                            'permissions' => $permissions->values()
                        ]);
                    });
                });

                


                
  

    // // Routes pour les services
    // Route::middleware(['auth:sanctum', 'permission:manage_services'])->group(function () {
    //     Route::resource('services', ServicesController::class);
    // });

   
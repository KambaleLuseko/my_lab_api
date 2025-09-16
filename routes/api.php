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
Route::apiResource('user-room-access', UserRoomAccessController::class);
Route::post('user-room-access/approve', [UserRoomAccessController::class, 'updateStatus']);
Route::post('user-room-access/reject', [UserRoomAccessController::class, 'rejectDemands']);
Route::post('user-room-access/cancel', [UserRoomAccessController::class, 'cancelDemands']);

Route::resource('services', ServicesController::class);

// // Routes de gestion de permission selon le rôle des utilisateurs
// Route::middleware(['role:admin'])->group(function () {
//     Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
// });

// Route::middleware(['role:etudiant'])->group(function () {
//     Route::get('/etudiant', [DashboardController::class, 'index'])->name('etudiant.dashboard');
// });

// Route::middleware(['role:enseignant'])->group(function () {
//     Route::get('/enseignant', [DashboardController::class, 'index'])->name('enseignant.dashboard');
// });
// use App\Models\User;



//     // Routes publiques (login, etc.)
//     // Route::post('/users/login', [UserController::class, 'login']);

//     Route::post('/login', function(Request $request) {
//     $user = User::where('email', $request->email)->first();

//     if (!$user || !Hash::check($request->password, $user->password)) {
//         return response()->json(['message' => 'Invalid credentials'], 401);
//     }

//     // Générer un token sanctum
//     $token = $user->createToken('api-token')->plainTextToken;

//         return response()->json([
//             'user' => $user->only('id','name','email'),
//             'token' => $token
//         ]);
//     });


//     // Routes accessibles uniquement aux admins
//     Route::middleware(['auth:sanctum', 'permission:manage_users'])->group(function () {
//         Route::get('/users', [UserController::class, 'index']);
//         Route::post('/users', [UserController::class, 'store']);
//         Route::get('/users/{id}', [UserController::class, 'show']);
//         Route::patch('/users/{id}', [UserController::class, 'update']);
//         Route::delete('/users/{id}', [UserController::class, 'destroy']);
//     });

//       // Routes pour gérer les salles (admin seulement)
//     Route::middleware(['auth:sanctum', 'permission:manage_rooms'])->group(function () {
//         Route::resource('salles', SallesController::class);
//         Route::apiResource('room-manager', RoomManagerController::class);
//     });

//      // Tester la permission ou assigner un rôle (admin seulement)
//     Route::middleware(['auth:sanctum', 'permission:manage_users'])->group(function () {
//         Route::post('/assign-role', function (Request $request) {
//             $user = User::findOrFail($request->user_id);
//             $user->roles()->syncWithoutDetaching([$request->role_id]);
//             return response()->json(['message' => 'Rôle attribué avec succès', 'user' => $user->load('roles')]);
//         });

//         Route::post('/check-permission', function (Request $request) {
//             $user = User::findOrFail($request->user_id);
//             $hasPermission = $user->roles()
//                 ->whereHas('permissions', function ($q) use ($request) {
//                     $q->where('name', $request->permission);
//                 })->exists();

//             return response()->json([
//                 'user' => $user->name,
//                 'permission' => $request->permission,
//                 'granted' => $hasPermission
//             ]);
//         });
//     });

  

//     // Routes pour les services
//     Route::middleware(['auth:sanctum', 'permission:manage_services'])->group(function () {
//         Route::resource('services', ServicesController::class);
//     });

   
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::all();
        return response()->json(['data'=>$users]);
    }
   /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'role' => 'required|string|max:255',
            'promotion' => 'nullable|string|max:255',
            'profile' => 'nullable|string|max:255',
            'annee_academique' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->uuid = Controller::uuidGenerator('USR');
        $user->name = $request->input('name');
        $user->phone = $request->input('phone');
        $user->address = $request->input('address');
        $user->role = $request->input('role');
        $user->promotion = $request->input('promotion');
        $user->profile = $request->input('profile');
        $user->annee_academique = $request->input('annee_academique');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->status = 'Active';
        $user->save();

        return response()->json(['message' => 'User created successfully!', 'data'=>$user], 200);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'role' => 'required|string|max:255',
            'promotion' => 'nullable|string|max:255',
            'profile' => 'nullable|string|max:255',
            'annee_academique' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        ]);

        $user = User::where('email', $request->email)->where('id', '!=', $id)->first();
        if ($user) {
            return response()->json(['message' => 'User with this email already exists'], 403);
        }
        $user=User::find($id);
        $user->name = $request->input('name');
        $user->phone = $request->input('phone');
        $user->address = $request->input('address');
        $user->role = $request->input('role');
        $user->promotion = $request->input('promotion');
        $user->profile = $request->input('profile');
        $user->annee_academique = $request->input('annee_academique');
        $user->email = $request->input('email');
         unset($user->uuid);
        $user->save();

        return response()->json(['message' => 'User updated successfully!']);
    }


    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();

        return response()->json(['message' => 'User deleted successfully!']);
    }
}

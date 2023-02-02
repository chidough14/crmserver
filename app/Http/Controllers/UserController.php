<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register (Request $request) {
        $request->validate([
            'name'=> 'required',
            'email'=> 'required|email',
            'password'=> 'required|confirmed',
            'tc'=> 'required',
        ]);

        if (User::where('email', $request->email)->first()) {
            return response([
              'message' => 'Email already exists',
              'status' => 'failed'
            ], 200);
        }

        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> Hash::make($request->password),
            'tc'=> json_decode($request->tc),
            'role'=> "user",
        ]);

        $token = $user->createToken($request->email)->plainTextToken;

        Settings::create([
            'user_id'=> $user->id,
            'calendar_mode'=> "week",
            'dashboard_mode'=> "show_graphs",
            'product_sales_mode'=> "allusers",
            'top_sales_mode'=> "salespersons"
        ]);

        Profile::create([
            'user_id'=> $user->id,
        ]);

        return response([
            'token' => $token,
            'user' => $user,
            'message' => 'Registration Success',
            'status' => 'success'
        ], 201);
    }

    public function login (Request $request) {
        $request->validate([
            'email'=> 'required|email',
            'password'=> 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken($request->email)->plainTextToken;

            return response([
                'user'=> $user,
                'token' => $token,
                'message' => 'Login Success',
                'status' => 'success'
            ], 201);
        }

        return response([
            'message' => 'Wrong Credentials',
            'status' => 'failed'
        ], 201);
    }

    public function logout () {
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'Logout successful',
            'status' => 'success'
        ], 201);
    }

    public function loggedUser () {
        $loggerdUser = auth()->user();
        $loggerdUser->setting;

        return response([
            'user' => $loggerdUser,
            'message' => 'Loggged user data',
            'status' => 'success'
        ], 201);
    }

    public function changePassword (Request $request) {
        $request->validate([
            'password'=> 'required|confirmed',
        ]);

        $loggerdUser = auth()->user();

        $loggerdUser->password = Hash::make($request->password);

        $loggerdUser->save();

        return response([
            'message' => 'Password changed successfully',
            'status' => 'success'
        ], 201);
    }
    
    public function getAllUsers () {
        $users = User::all();

        return response([
            'users'=> $users,
            'message' => 'All Users',
            'status' => 'success'
        ], 201);
    }

    public function updateUserDetails (Request $request, $id) {
        $user = User::where("id", $id)->first();

        $user->update($request->all());

        $user->setting;

        return response([
            'user'=> $user,
            'message' => 'Updated User Details',
            'status' => 'success'
        ], 201);
    }
}

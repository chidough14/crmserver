<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function updateProfile (Request $request) {
        $user = User::with('profile')->findOrFail(auth()->user()->id);
        if ($user->profile === null)
        {
            //$profile = new Profile($request->all());
            $user->profile()->create($request->all());
        }
        else
        {
            $user->profile->update($request->all());
        }

        $res = Profile::where("user_id", $user->id)->first();

        return response([
            'profile'=> $res,
            'message' => 'Profile updated successfully',
            'status' => 'success'
        ], 201);
    }

    public function getUserProfile ($user_id) {
        $profile = Profile::where("user_id", $user_id)->first();
       
        return response([
            'profile'=> $profile,
            'message' => 'User Profile',
            'status' => 'success'
        ], 201);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function updateUserDetails (Request $request, $id) {
        $user = User::where("id", $id)->first();

        if (auth()->user()->role === "admin" || auth()->user()->role === "super admin" ) {
            $user->update($request->all());

            $user->setting;
    
            return response([
                'user'=> $user,
                'message' => 'Updated User Details',
                'status' => 'success'
            ], 201);
        
        } else {
            return response([
                'message' => 'Not Allowed',
                'status' => 'success'
            ], 201);
        }

        
    }

    public function deleteUser ($id) {
        $user = User::where("id", $id)->first();

        if (auth()->user()->role !== "admin") {
            return response([
                'message' => 'Not Allowed',
                'status' => 'success'
            ], 201);
        } else {
            $user->delete();
    
            return response([
                'message' => 'User deleted',
                'status' => 'success'
            ], 201);
        }

        
    }
}

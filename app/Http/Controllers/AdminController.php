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

    public function bulkDeleteUsers (Request $request) {

        if (auth()->user()->role === "admin" || auth()->user()->role === "super admin") {
            User::whereIn('id', $request->userIds)->delete();
    
            return response([
                'message' => 'Users deleted',
                'status' => 'success'
            ], 201);
         
        } else {
            return response([
                'message' => 'Not Allowed',
                'status' => 'success'
            ], 201);
        }
    }
    

    public function bulkUpdateUsers (Request $request) {
        $arr = [];

        if (auth()->user()->role === "admin" || auth()->user()->role === "super admin") {
            foreach ($request->userIds as $user) {
                if ($user['role'] === 'super admin') {
                  
                } elseif ($user['role'] === 'admin') {
                    User::where('id', $user['id'])->update(['role' => 'user']);
                    $arr[] = User::where('id', $user['id'])->first();
                } elseif ($user['role'] === 'user') {
                    User::where('id', $user['id'])->update(['role' => 'admin']);
                    $arr[] = User::where('id', $user['id'])->first();
                }
            }
    
            return response([
                "users"=> $arr,
                'message' => 'Users roles updated',
                'status' => 'success'
            ], 201);
         
        } else {
            return response([
                'message' => 'Not Allowed',
                'status' => 'success'
            ], 201);
        }
    }
}

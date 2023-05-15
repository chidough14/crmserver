<?php

namespace App\Http\Controllers;

use App\Models\Logout;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function addlogout (Request $request) {
        $record = Logout::create($request->all());

        return response([
            'record'=> $record,
            'message' => 'Record created successfully',
            'status' => 'success'
        ], 201);
    }

    public function userlogout ($id) {
        $record = Logout::where("user_id", $id)->latest('created_at')->first();

        return response([
            'record'=> $record,
            'message' => 'Record created successfully',
            'status' => 'success'
        ], 201);
    }
}

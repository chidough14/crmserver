<?php

namespace App\Http\Controllers;

use App\Models\Userschat;
use Illuminate\Http\Request;

class UserschatController extends Controller
{
    public function getChats ($id) {
        $chats = Userschat::where("conversation_id", $id)->get();

        return response([
            'chats'=> $chats,
            'message' => 'Chats',
            'status' => 'success'
        ], 201);
    }

    public function addChats (Request $request) {
        $chat = Userschat::create([
            "message"=> $request->message,
            "user_id"=> $request->user_id,
            "recipient_id"=> $request->recipient_id,
            "conversation_id"=> $request->conversation_id
        ]);

        return response([
            'chat'=> $chat,
            'message' => 'Chat created',
            'status' => 'success'
        ], 201);
    }
}

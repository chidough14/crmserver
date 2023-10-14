<?php

namespace App\Http\Controllers;

use App\Models\Adminchat;
use Illuminate\Http\Request;

class AdminchatController extends Controller
{
    public function getChats ($id) {
        $chats = Adminchat::where("conversation_id", $id)->get();

        return response([
            'chats'=> $chats,
            'message' => 'Chats',
            'status' => 'success'
        ], 201);
    }

    public function addChats (Request $request) {
        $chat = Adminchat::create([
            "message"=> $request->message,
            "user_id"=> $request->user_id,
            "conversation_id"=> $request->conversation_id
        ]);

        return response([
            'chat'=> $chat,
            'message' => 'Chat created',
            'status' => 'success'
        ], 201);
    }

    public function uploadFile(Request $request)
    {

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filePath = $file->storeAs('files', $originalName, 'public');

        $adminchat = Adminchat::create([
            "message"=> $request->message,
            "user_id"=> $request->user_id,
            "conversation_id"=> $request->conversation_id,
            "files" => $filePath
        ]);

        return response()->json(['filePath' => $filePath, 'chat' => $adminchat]);
  
    }
}

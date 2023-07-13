<?php

namespace App\Http\Controllers;

use App\Models\offlineFollowersMessage;
use Illuminate\Http\Request;

class OfflineFollowersMessageController extends Controller
{
    public function addMessage (Request $request) {
        for ($i = 0; $i < count($request->arr); $i++) {
            offlineFollowersMessage::create([
                "follower_id" => $request->arr[$i]['id'],
                "message" => $request->arr[$i]['message'],
            ]);
        }

        return response([
            'message' => 'Messages added',
            'status' => 'success'
        ], 201);
    }

    public function getMessages () {
        $messages = offlineFollowersMessage::where("follower_id", auth()->user()->id)->where("isRead", false)->get();

        return response([
            "followersData" => $messages,
            'message' => 'Messages added',
            'status' => 'success'
        ], 201);
    }

    public function deleteMessage ($id) {
        offlineFollowersMessage::where("id", $id)->delete();

        return response([
            'message' => 'Message deleted',
            'status' => 'success'
        ], 201);
    }
}

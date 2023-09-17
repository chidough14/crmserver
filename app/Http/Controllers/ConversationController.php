<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function addConversation (Request $request) {
        $conversation = Conversation::create([
            'conversation_string'=> $request->conversation_string,
            'user_id' => auth()->user()->id
        ]);

        return response([
            'conversation'=> $conversation,
            'message' => 'Conversation created successfully',
            'status' => 'success'
        ], 201);
    }

    public function fetchConversations () {
        $conversations = Conversation::where("user_id", auth()->user()->id)->latest('created_at')->get();

        return response([
            'conversations'=> $conversations,
            'message' => 'All Conversations',
            'status' => 'success'
        ], 201);
    }
}

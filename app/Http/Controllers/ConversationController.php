<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function addConversation (Request $request) {
        $conversation = Conversation::create([
            'conversation_string'=> $request->conversation_string,
            'user_id' => auth()->user()->id,
            'recipient_id' => $request->recipient_id 
        ]);

        return response([
            'conversation'=> $conversation,
            'message' => 'Conversation created successfully',
            'status' => 'success'
        ], 201);
    }

    public function fetchConversations ($mode) {
        $loggedInUserId = auth()->user()->id;

        if ($mode === "users") {
            $conversations = Conversation::where(function ($query) use ($loggedInUserId) {
                $query->where('user_id', $loggedInUserId);
                    //   ->orWhere('recipient_id', $loggedInUserId);
            })
            ->whereNotNull('recipient_id')
            ->latest('created_at')
            ->paginate(5);
        } else {
            $conversations = Conversation::where("user_id", $loggedInUserId)->whereNull('recipient_id')->latest('created_at')->get();
        }
       

        return response([
            'conversations'=> $conversations,
            'message' => 'All Conversations',
            'status' => 'success'
        ], 201);
    }

    public function deleteConversation ($id) {
        $conversation = Conversation::where("id", $id)->first();

        if ($conversation->user_id === auth()->user()->id) {
            $conversation->delete();
        

            return response([
                'message' => 'Conversation deleted',
                'status' => 'success'
            ], 201);
        } else {
            return response([
                'message' => 'You are not authorized to delete this item',
                'status' => 'Unauthorized'
            ], 401);
        }
    }

    public function bulkDeleteConversations (Request $request) {
        Conversation::whereIn('id', $request->messageIds)->delete();

        return response([
            'message' => 'Conversations deleted',
            'status' => 'success'
        ], 201);
    }
}

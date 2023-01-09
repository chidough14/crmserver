<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function createMessage (Request $request) {
        $request->validate([
            'subject'=> 'required',
            'message'=> 'required'
        ]);

        $message = Message::create($request->all());

        return response([
            'createdMessage'=> $message,
            'message' => 'Message created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getMessages () {
        $outBoxMessages = Message::where("sender_id", auth()->user()->id)->get();

        $inBoxMessages = Message::where("receiver_id", auth()->user()->id)->get();

        return response([
            'inbox'=> $inBoxMessages,
            'outbox' => $outBoxMessages,
            'message' => 'Your messages',
            'status' => 'success'
        ], 201);

    }

    public function getSingleMessage ($messageId) {
        $message = Message::where("id", $messageId)->first();

        return response([
            'messageDetails'=> $message,
            'message' => 'Message',
            'status' => 'success'
        ], 201);

    }

    public function updateMessage (Request $request, $messageId) {
        $message = Message::where("id", $messageId)->first();

        if (($message->sender_id !== auth()->user()->id) || $message->isRead) {

            return response([
                'message' => 'Not allowed',
                'status' => 'success'
            ], 201);
        } else {
            $request->validate([
                'subject'=> 'required',
                'message'=> 'required'
            ]);

            $message->update($request->all());

            return response([
                'messageDetails'=> $message,
                'message' => 'Message updated',
                'status' => 'success'
            ], 201);
        }

    }

    public function deleteMessage ($messageId) {
        $message = Message::where("id", $messageId)->first();

        if (($message->sender_id !== auth()->user()->id)) {

            return response([
                'message' => 'Not allowed',
                'status' => 'success'
            ], 201);
        } else {

            $message->delete();

            return response([
                'message' => 'Message deleted',
                'status' => 'success'
            ], 201);
        }

    }

    public function readMessage (Request $request, $messageId) {
        $message = Message::where("id", $messageId)->first();

        if (($message->receiver_id !== auth()->user()->id)) {

            return response([
                'message' => 'Not allowed',
                'status' => 'success'
            ], 201);
        } else {

            $message->isRead = $request->isRead;
            $message->save();

            return response([
                'messageDetails'=> $message,
                'message' => 'Message read',
                'status' => 'success'
            ], 201);
        }

    }
}

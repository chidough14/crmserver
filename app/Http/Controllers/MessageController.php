<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message as MailMessage;
use Illuminate\Support\Str; 

class MessageController extends Controller
{
    public function createMessage (Request $request) {
        if (is_array($request->receiver_id)) {
            $messageArray = array();
            for ($i=0; $i < count($request->receiver_id); $i++) {
              $res = new Message();
              $res->subject = $request->subject;
              $res->message = $request->message;
              $res->sender_id = $request->sender_id;
              $res->receiver_id = $request->receiver_id[$i];

              $res->save();

              array_push($messageArray, $res);

              $user = User::where("id", $request->receiver_id[$i])->first();
              $email = $user->email;

              Mail::send('message', ['id'=> $res->id], function (MailMessage $message) use ($email) {
                    $message->subject('New Message');
                    $message->to($email);
                });
            }

            return response([
                'createdMessages'=> $messageArray,
                'message' => 'Messages created successfully',
                'status' => 'success'
            ], 201);

        } else {
            $request->validate([
                'subject'=> 'required',
                'message'=> 'required'
            ]);

            $resp = Message::create($request->all());

            $user = User::where("id", $request->receiver_id)->first();
            $email = $user->email;



            // Sending email 
            Mail::send('message', ['id'=> $resp->id], function (MailMessage $message) use ($email) {
                $message->subject('New Message');
                $message->to($email);
            });

            return response([
                'createdMessage'=> $resp,
                'message' => 'Message created successfully',
                'status' => 'success'
            ], 201);
        }
    }

    public function getInboxMessages () {

        $inBoxMessages = Message::where("receiver_id", auth()->user()->id)->orderBy('created_at', 'DESC')->paginate(5);

        return response([
            'inbox'=> $inBoxMessages,
            'message' => 'Your messages',
            'status' => 'success'
        ], 201);

    }

    public function getOutboxMessages () {
        $outBoxMessages = Message::where("sender_id", auth()->user()->id)->orderBy('created_at', 'DESC')->paginate(5);

        return response([
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

            $message->delete();

            return response([
                'message' => 'Message soft deleted',
                'status' => 'success'
            ], 201);
        } else {

            $message->forceDelete();

            return response([
                'message' => 'Message deleted',
                'status' => 'success'
            ], 201);
        }

    }

    public function massDeleteMessages (Request $request) {

        if ($request->mode === "inbox") {
            Message::whereIn('id', $request->messageIds)->delete();

            return response([
                'message' => 'Messages soft deleted',
                'status' => 'success'
            ], 201);
         
        } else {
            Message::whereIn('id', $request->messageIds)->forceDelete();

            return response([
                'message' => 'Messages  deleted',
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

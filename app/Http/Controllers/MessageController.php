<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message as MailMessage;
use Illuminate\Support\Str; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    public function createMessage (Request $request) {
        if (is_array($request->receiver_id)) {
            $messageArray = array();

            $requestObject = $request->all();

            if (isset($requestObject['files'])) {
                $files = $requestObject['files'];
                $requestObject['files'] = json_encode($files);
            }
            $requestObject['quill_message'] = json_encode($requestObject['quill_message']);


            for ($i=0; $i < count($request->receiver_id); $i++) {
              $res = new Message();
              $res->subject = $requestObject['subject'];
              $res->message =  $requestObject['message'];
              $res->sender_id =  $requestObject['sender_id'];
              $res->quill_message = $requestObject['quill_message'];
              $res->receiver_id = $request->receiver_id[$i];
              $res->files =  $requestObject['files'];

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

            $requestObject = $request->all();

            if (isset($requestObject['files'])) {
                $files = $requestObject['files'];
                $requestObject['files'] = json_encode($files);
            }

            $requestObject['quill_message'] = json_encode($requestObject['quill_message']);

            $resp = Message::create($requestObject);

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
        $lastFetchTimestamp = Cache::get('last_fetch_timestamp');

        $inBoxMessages = Message::where("receiver_id", auth()->user()->id)->orderBy('created_at', 'DESC')->paginate(5);

        foreach ($inBoxMessages as $record) {
            $record->files = json_decode($record->files);
            // $record->quill_message = json_decode($record->quill_message);
            if ($record->created_at >= $lastFetchTimestamp) {
                // This is a newly added record
                // You can handle it accordingly
                $record->new = true;
            }
        }

        // Get the current timestamp
        $currentTimestamp = Carbon::now();

        // Update the last fetch timestamp with the current timestamp
        $lastFetchTimestamp = $currentTimestamp;

        Cache::put('last_fetch_timestamp', $lastFetchTimestamp);

        return response([
            'inbox'=> $inBoxMessages,
            'message' => 'Your messages',
            'status' => 'success'
        ], 201);

    }

    public function getOutboxMessages () {
        $outBoxMessages = Message::where("sender_id", auth()->user()->id)->orderBy('created_at', 'DESC')->paginate(5);

        foreach ($outBoxMessages as $record) {
            $record->quill_message = json_decode($record->quill_message);
            $record->files = json_decode($record->files);
        }
     

        return response([
            'outbox' => $outBoxMessages,
            'message' => 'Your messages',
            'status' => 'success'
        ], 201);

    }

    public function getSingleMessage ($messageId) {
        $message = Message::where("id", $messageId)->first();

        if ($message->sender_id !== auth()->user()->id && $message->isRead === 0) {
            $message->isRead = 1;
            $message->save();
        }

        $message->files = json_decode($message->files);

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
        $arr = [];

        foreach ($request->messageIds as $item) {
            $arr[] = $item["id"];
        }

        if ($request->mode === "inbox") {
            Message::whereIn('id', $arr)->delete();

            return response([
                'message' => 'Messages soft deleted',
                'status' => 'success'
            ], 201);
         
        } else {
            Message::whereIn('id', $arr)->forceDelete();

            return response([
                'message' => 'Messages  deleted',
                'status' => 'success'
            ], 201);
            
        }

    }

    public function massReadMessages (Request $request) {

        $readTrueArray = [];
        $readFalseArray = [];

        foreach ($request->messageIds as $item) {
            if ($item['read']) {
                $readTrueArray[] = $item["id"];
            } else {
                $readFalseArray[] = $item["id"];
            }
        }

        Message::whereIn('id', $readFalseArray)->update(["isRead" => true]);
        Message::whereIn('id', $readTrueArray)->update(["isRead" => false]);

        return response([
            'message' => 'Messages updated',
            'status' => 'success'
        ], 201);
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

<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Message;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private function replaceMentions($inputString) {
        $pattern = '/@\[(.*?)\]\((.*?)\)/';
        $replacement = '$1';
        $outputString = preg_replace($pattern, $replacement, $inputString);
        return $outputString;
    }

    private function sendMessage ($mentions,  $content, $activityId) {
        $allUsers = User::all();
        $userData = null;

        for ($i=0; $i<count($mentions); $i++) {
           //    $username = substr($request->mentions[$i], 1);
           foreach ($allUsers as $person) {
                if ($person->name === $mentions[$i]) {
                    $userData = $person;
                    break; // Stop searching once the target is found
                }
            }

            if ($userData !== null) {
                Message::create([
                    "message" => "Activity ID (".$activityId.")",
                    "subject" => "You were mentioned by ".auth()->user()->name,
                    "quill_message" => json_encode($content),
                    "receiver_id" => $userData->id
                ]);
            } else {
              return "Person not found";
            }
   
        }
    }


    public function addComment (Request $request) {
        $request->validate([
            'content'=> 'required',
            'activity_id'=> 'required',
        ]);

        $comment = Comment::create([
            // 'content'=> $request->content,
            'content'=> json_encode($request->content),
            'user_id'=> auth()->user()->id,
            'activity_id'=> $request->activity_id,
            'parent_id'=> $request->parent_id,
            'upvotes' => 0,
            'files' => json_encode($request->paths)
        ]);

        $this->sendMessage($request->mentions, $request->content, $request->activity_id);

        $comment->content = json_decode( $comment->content);
        $comment->files = json_decode( $comment->files);

        return response([
            'comment'=> $comment,
            'message' => 'Comment created successfully',
            'status' => 'success'
        ], 201);
    }

    public function editComment (Request $request, $id) {

        $comment = Comment::where("id", $id)->first();

        $comment->content = $request->content;
        $comment->files = json_encode($request->paths);
        $comment->save();

        $this->sendMessage($request->mentions, $request->content, $request->activity_id);

        $comment->files = json_decode( $comment->files);
        
        return response([
            'comment'=> $comment,
            'message' => 'Comment updated successfully',
            'status' => 'success'
        ], 201);
    }

    public function deleteComment ($id) {

        $comment = Comment::where("id", $id)->first();

        $comment->isDeleted = "Yes";
        $comment->save();

        return response([
            'comment'=> $comment,
            'message' => 'Comment deleted',
            'status' => 'success'
        ], 201);
    }

    public function upVote ($id) {
        $comment = Comment::findOrFail($id);
        $allUsers = User::all();

        $upvote = Vote::where("user_id", auth()->user()->id)
                ->where("comment_id", $id)
                ->where("vote_type", "upvote")
                ->first();  
     
        $likers = $comment->likersId ?? [];

        if ($upvote) {
            $comment->upvotes =  $comment->upvotes - 1;

            $likers = array_diff($likers, [auth()->user()->id]);
            $comment->likersId = $likers;

            $comment->save();
            $upvote->delete();
            $comment->content = json_decode( $comment->content);
            
            $newArray = [];
            foreach ($allUsers as $item2) {
               
                if (in_array($item2['id'], $comment->likersId)) {
                    $newArray[] = $item2;
                }
              
            }
            $comment->likers = $newArray;
            $comment->files = json_decode( $comment->files);

            return response([
                'comment'=> $comment,
                'message' => 'Already upvoted',
                'status' => 'success'
            ], 201);
        } else {
            $comment->upvote();
          
            $this->storeVote($comment, 'upvote');

            $likers[] = auth()->user()->id;
            $comment->likersId = $likers;
            $comment->save();

            $comment->content = json_decode( $comment->content);

            $newArray = [];
            foreach ($allUsers as $item2) {
               
                if (in_array($item2['id'], $comment->likersId)) {
                    $newArray[] = $item2;
                }
              
            }
            $comment->likers = $newArray;
            $comment->files = json_decode( $comment->files);

            return response([
                'comment'=> $comment,
                'message' => 'Comment upvoted',
                'status' => 'success'
            ], 201);
        }      
      
    }

    public function downVote ($id) {
        $comment = Comment::findOrFail($id);

        $downvote = Vote::where("user_id", auth()->user()->id)
        ->where("comment_id", $id)
        ->where("vote_type", "downvote")
        ->first();

        if ($downvote) {
            $comment->downvotes = $comment->downvotes - 1;
            $comment->save();
            $downvote->delete();
            $comment->content = json_decode( $comment->content);
            $comment->files = json_decode( $comment->files);

            return response([
                'comment'=> $comment,
                'message' => 'Already downvoted',
                'status' => 'success'
            ], 201);
        } else {
            $comment->downvote();
       
            $this->storeVote($comment, 'downvote');
            $comment->content = json_decode( $comment->content);
            $comment->files = json_decode( $comment->files);

            return response([
                'comment'=> $comment,
                'message' => 'Comment downvoted',
                'status' => 'success'
            ], 201);
        } 
    }

    private function storeVote($comment, $type)
    {
        $vote = new Vote(['vote_type' => $type, 'user_id'=> auth()->user()->id]);
        $comment->votes()->save($vote);
    }

    public function getUserDownvotes () {
        $downvotes = Vote::where("user_id", auth()->user()->id)
        ->where("vote_type", "downvote")
        ->get();

        
        return response([
            'downvotes'=> $downvotes,
            'message' => 'Downvotes',
            'status' => 'success'
        ], 201);
    }

    public function getUserUpvotes () {
        $upvotes = Vote::where("user_id", auth()->user()->id)
        ->where("vote_type", "upvote")
        ->get();

        
        return response([
            'upvotes'=> $upvotes,
            'message' => 'Upvotes',
            'status' => 'success'
        ], 201);
    }

    public function uploadFiles(Request $request)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filePath = $file->storeAs('files', $originalName, 'public'); 
        return response()->json(['filePath' => $filePath]);
    }

    public function download($filename)
    {
        $filePath = storage_path('app/public/files/' . $filename);
        return response()->download($filePath);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function addComment (Request $request) {
        $request->validate([
            'content'=> 'required',
            'activity_id'=> 'required',
        ]);

        $comment = Comment::create([
            'content'=> $request->content,
            'user_id'=> auth()->user()->id,
            'activity_id'=> $request->activity_id,
            'parent_id'=> $request->parent_id,
        ]);

        return response([
            'comment'=> $comment,
            'message' => 'Comment created successfully',
            'status' => 'success'
        ], 201);
    }

    public function editComment (Request $request, $id) {

        $comment = Comment::where("id", $id)->first();

        $comment->content = $request->content;
        $comment->save();

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
}

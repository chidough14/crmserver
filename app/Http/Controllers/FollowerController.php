<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;

class FollowerController extends Controller
{
    public function followUser (Request $request) {
        $record = Follower::where("follower_id", auth()->user()->id)
        ->where("followee_id", $request->followee_id)->first();

        if ($record) {
            return response([
                'message' => 'User already followed',
                'status' => 'success'
            ], 201);
        } else {
            $rec = Follower::create([
                'follower_id'=> auth()->user()->id,
                'followee_id'=> $request->followee_id
            ]);
    
            return response([
                'record' => $rec,
                'message' => 'User followed successfully',
                'status' => 'success'
            ], 201);
        }
       
    }

    public function unFollowUser (Request $request) {
        $record = Follower::where("follower_id", auth()->user()->id)
                ->where("followee_id", $request->followee_id)->first();

        if ($record) {
            $record->delete();

            return response([
                'message' => 'User unfollowed',
                'status' => 'success'
            ], 201);
        } else {
            return response([
                'message' => 'Not following user',
                'status' => 'success'
            ], 201);
        }
    }

    public function getMyFollowers () {
        $user = User::find(auth()->user()->id);
        $followers = $user->followers;

        return response([
            'followers'=> $followers,
            'message' => 'Users following you',
            'status' => 'success'
        ], 201);
    }

    public function getMyFollowed () {
        $user = User::find(auth()->user()->id);
        $followed = $user->following;

        return response([
            'followed'=> $followed,
            'message' => 'Users you followed',
            'status' => 'success'
        ], 201);
    }

    public function getUserFollowers ($id) {
        $user = User::find($id);
        $followers = $user->followers;

        return response([
            'followers'=> $followers,
            'message' => 'Users you followed',
            'status' => 'success'
        ], 201);
    }

    public function getUserFollowed ($id) {
        $user = User::find($id);
        $followed = $user->following;

        return response([
            'followed'=> $followed,
            'message' => 'Users you followed',
            'status' => 'success'
        ], 201);
    }
}

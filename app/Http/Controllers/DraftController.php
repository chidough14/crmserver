<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use Illuminate\Http\Request;

class DraftController extends Controller
{
    public function getDrafts () {
        $drafts = Draft::where("user_id", auth()->user()->id)->paginate(5);

        return response([
            'drafts'=> $drafts,
            'message' => 'All Drafts',
            'status' => 'success'
        ], 201);
    }

    public function addDraft (Request $request) {
        $draft = Draft::create([
            'user_id'=> auth()->user()->id,
            'message' => json_encode($request->message),
            'subject' => $request->subject
        ]);

        return response([
            'draft'=> $draft,
            'message' => 'Draft created',
            'status' => 'success'
        ], 201);
    }

    public function getDraft ($id) {
        $draft = Draft::where("id", $id)->first();

        return response([
            'draft'=> $draft,
            'message' => 'Draft',
            'status' => 'success'
        ], 201);
    }

    public function updateDraft (Request $request, $id) {
        $draft = Draft::where("id", $id)->first();

        $draft->subject = $request->subject;
        $draft->message = json_encode($request->message);
        $draft->save();

        // $draft->update($request->all());

        return response([
            'draft'=> $draft,
            'message' => 'Draft updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteDraft ($id) {
        $draft = Draft::where("id", $id)->first();

        $draft->delete();

        return response([
            'message' => 'Draft deleted',
            'status' => 'success'
        ], 201);
    }
}

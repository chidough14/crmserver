<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function addLabel (Request $request) {
        $label = Label::create([
            'user_id'=> auth()->user()->id,
            'name' => $request->name,
            'parent' => $request->parent,
        ]);

        return response([
            'label'=> $label,
            'message' => 'Label created',
            'status' => 'success'
        ], 201);
    }

    public function getLabels () {
        $labels = Label::where("user_id", auth()->user()->id)->get();

        return response([
            'labels'=> $labels,
            'message' => 'All Labels',
            'status' => 'success'
        ], 201);
    }

    public function updateLabel (Request $request, $id) {
        $label = Label::where("id", $id)->first();

        $label->name = $request->name;
        $label->save();

        return response([
            'label'=> $label,
            'message' => 'Label updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteLabel ($id) {
        $label = Label::where("id", $id)->first();

        $label->delete();

        $subLabels = Label::where('parent', $id)->get();

        if (count($subLabels)) {
            foreach ($subLabels as $l) {
                $l->delete();
            }
        }

        // $label->delete();

        return response([
            'message' => 'Label deleted',
            'status' => 'success'
        ], 201);
    }
}

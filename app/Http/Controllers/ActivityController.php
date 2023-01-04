<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function createActivity (Request $request) {
        $request->validate([
            'label'=> 'required',
            'assignedTo'=> 'required',
            'earningEstimate'=> 'required',
            'type'=> 'required',
            'probability'=> 'required'
        ]);

        $activity = Activity::create($request->all());

        Event::create([
            "title" => "{$request->type} with {$request->label}",
            "description" => "Auto generated",
            "user_id" => auth()->user()->id,
            "activity_id" =>  $activity->id,
            "start" => Carbon::parse($activity->created_at)->toDateTimeString() ,
            "end" => Carbon::parse($activity->created_at)->addHours(1)->toDateTimeString()
        ]);

        return response([
            'activity'=> $activity,
            'message' => 'Activity created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getActivities () {

        $activities = Activity::all();

        return response([
            'activities'=> $activities,
            'message' => 'All activities',
            'status' => 'success'
        ], 201);
    }

    public function getSingleActivity ($activityId) {

        $activity = Activity::where("id", $activityId)->first();

        $activity->products;
        $activity->events;

        return response([
            'activity'=> $activity,
            'message' => 'Activity',
            'status' => 'success'
        ], 201);
    }

    public function updateActivity (Request $request, $activityId) {

        $activity = Activity::where("id", $activityId)->first();

        $activity->update($request->all());

        return response([
            'activity'=> $activity,
            'message' => 'Activity updated',
            'status' => 'success'
        ], 201);
    }


    public function deleteActivity ($activityId) {

        $activity = Activity::where("id", $activityId)->first();

        $activity->delete();

        return response([
            'message' => 'Activity deleted',
            'status' => 'success'
        ], 201);
    }

    public function addUpdateProduct (Request $request, $activityId) {

        $activity = Activity::where("id", $activityId)->first();
        $productId = $request->productId;
        $quantity = $request->quantity;

        $activity->products()->sync([$productId => [ 'quantity' => $quantity] ], false);

        return response([
            'product'=> $activity->products()->where('product_id', $productId)->first(),
            'message' => 'Product added',
            'status' => 'success'
        ], 201);
    }

    public function deleteProduct (Request $request, $activityId) {

        $activity = Activity::where("id", $activityId)->first();
        $activity->products()->detach($request->productId);
      
        return response([
            'message' => 'Product deleted',
            'status' => 'success'
        ], 201);
    }
}

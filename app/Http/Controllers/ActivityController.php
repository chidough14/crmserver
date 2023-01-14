<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\User;
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

        $event = Event::create([
            "title" => "{$request->type} with {$request->label}",
            "description" => "Auto generated",
            "user_id" => auth()->user()->id,
            "activity_id" =>  $activity->id,
            "start" => Carbon::parse($activity->created_at)->toDateTimeString() ,
            "end" => Carbon::parse($activity->created_at)->addHours(1)->toDateTimeString()
        ]);

        return response([
            'activity'=> $activity,
            'event'=> $event,
            'message' => 'Activity created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getActivities () {

        $activities = Activity::where("user_id", auth()->user()->id)->get();

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
        $activity->company;
        $activity->invoices;

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

    function createClone ($activity, $ownActivity, $transfer) {
        $activity->products;

        $arr = array();
        $arrQty = array();
        $arrInvoices = array();

        for ($i=0; $i < count($activity->products); $i++) { // copy products idd
          $arr[] = $activity->products[$i]->id;
          $arrQty[] = $activity->products[$i]->pivot->quantity;
        }

        for ($a=0; $a < count($activity->invoices); $a++) { // copy invoice ids
            $arrInvoices[] = $activity->invoices[$a]->id;
        }

        $sync_data = [];
        for($j = 0; $j < count($arr); $j++)
            $sync_data[$arr[$j]] = ['quantity' => $arrQty[$j]];

        //$invoice->products()->attach($sync_data);

        $clonedActivity = $activity;
        unset($clonedActivity->id);

        $newActivity = new Activity();
        if ($transfer) {
            $newActivity->user_id = $transfer->id; 
        } else {
            $newActivity->user_id = ($ownActivity === true) ? $clonedActivity->user_id : auth()->user()->id; 
        }
        $newActivity->label = $clonedActivity->label."_Copy"; 
        $newActivity->description = $clonedActivity->description;
        $newActivity->type = $clonedActivity->type; 
        // $newActivity->user_id = ($ownActivity === true) ? $clonedActivity->user_id : auth()->user()->id; 
        $newActivity->assignedTo = $clonedActivity->assignedTo; 
        $newActivity->probability = $clonedActivity->probability; 
        $newActivity->earningEstimate = $clonedActivity->earningEstimate; 
        $newActivity->company_id = $clonedActivity->company_id; 
        $newActivity->status = $clonedActivity->status;  
        $newActivity->save();

        $newActivity->products()->attach($sync_data);
       
        for ($c=0; $c < count($activity->invoices); $c++) { 
            $inv = Invoice::where("id", $activity->invoices[$c]->id)->first();
            $inv->products;

            $idss = array();
            $qtys = array();
            for ($d=0; $d < count($inv->products); $d++) { // copy products idd
                $idss[] = $inv->products[$d]->id;
                $qtys[] = $inv->products[$d]->pivot->quantity;
            }

            $sync_data2 = [];
            for($e = 0; $e < count($idss); $e++)
                $sync_data2[$idss[$e]] = ['quantity' => $qtys[$e]];

            $clonedInv = $inv;
            unset($clonedInv->id);

            $newInv = new   Invoice();
            if ($transfer) {
                $newInv->user_id = $transfer->id; 
            } else {
                $newInv->user_id = ($ownActivity === true) ? $clonedInv->user_id : auth()->user()->id; 
            }
            $newInv->invoice_no = $clonedInv->invoice_no; 
            $newInv->payment_method = $clonedInv->payment_method;
            $newInv->billing_address = $clonedInv->billing_address; 
            //$newInv->user_id = ($ownActivity === true) ? $clonedInv->user_id : auth()->user()->id; 
            $newInv->reference = $clonedInv->reference; 
            $newInv->type = $clonedInv->type; 
            $newInv->activity_id = $newActivity->id;
            $newInv->status = $clonedInv->status; 
            $newInv->payment_term = $clonedInv->payment_term;
            $newInv->email = $clonedInv->email;

            $newInv->save();
            $newInv->products()->attach($sync_data2);

        }
        

        return $newActivity;
    }

    public function cloneActivity ($activityId) {

        $activity = Activity::where("id", $activityId)->first();

        if ($activity->user_id === auth()->user()->id) {
            $ownActivity = true;
            $transfer = false;
            $res = $this->createClone($activity, $ownActivity, $transfer);

            return response([
                'clonedActivity'=> $res,
                'message' => 'Activity',
                'status' => 'success'
            ], 201);
        } else {
            if ($activity->status === "private") {

                return response([
                    'message' => 'Not allowed',
                    'status' => 'success'
                ], 201);

            } else {
                $ownActivity = false;
                $transfer = false;
                $response = $this->createClone($activity, $ownActivity, $transfer);

                return response([
                    'clonedActivity'=> $response,
                    'message' => 'Activity',
                    'status' => 'success'
                ], 201);
            }
        }
    }

    public function transferActivity (Request $request, $activityId) {
        $activity = Activity::where("id", $activityId)->first();

        $newOwner = User::where("email", $request->email)->first();

        $ownActivity = true;

        $transferedActivity = $this->createClone($activity, $ownActivity, $newOwner );

        $res = new Message();
        $res->subject = "Activity Tranfer";
        $res->message = "An activity $transferedActivity->label ($transferedActivity->id) has been transfered to you";
        $res->receiver_id =  $newOwner->id;

        $res->save();

        return response([
            'message' => 'Activity Transfered',
            'status' => 'success'
        ], 201);
    }
}

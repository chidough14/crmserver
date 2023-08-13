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

    public function getTotals ($activities) {
        foreach ($activities as $activity) {
            if ($activity->probability === "Closed") {
                $total = 0;
            
                if (count($activity->invoices)) {
                    $sortedInvoices = $activity->invoices->sortBy('created_at')->values();
                    $inv = Invoice::with('products')->where("id", $sortedInvoices[count($sortedInvoices) - 1]->id)->first();
                    
                
                    foreach ($inv->products as $product) {
                        $total += $product['price'] * $product['pivot']['quantity'];
                    }
                    
                
                    $activity['total'] = $total;
                } else {
                    $activity['total'] = 0;
                }
            

            } else {
                $total = 0;
                foreach ($activity['products'] as $product) {
                    $total += $product['price'] * $product['pivot']['quantity'];
                }
                $activity['total'] = $total;
            }

            $activity->comments;
          
        }

        foreach ($activities as &$item) {
            unset($item['products']);
            unset($item['invoices']);
        }

        return $activities;
    }

    public function getActivities () {

        $activities = Activity::with('products')->where("user_id", auth()->user()->id)->get();
     

        $res = $this->getTotals($activities);
        
        return response([
            'activities'=> $res,
            'message' => 'All activities',
            'status' => 'success'
        ], 201);
    }

    public function filterActivities ($critera) {

        if ($critera === "1month") {
            $activities = Activity::where("user_id", auth()->user()->id)
            ->where('created_at', '>', now()->subDays(30)->endOfDay())
            ->get();
        } 

        if ($critera === "3months") {
            $activities = Activity::where("user_id", auth()->user()->id)
            ->where('created_at', '>', now()->subDays(60)->endOfDay())
            ->get();
        } 

        if ($critera === "12months") {
            $activities = Activity::where("user_id", auth()->user()->id)
            ->where('created_at', '>', now()->subDays(365)->endOfDay())
            ->get();
        } 


        $res = $this->getTotals($activities);

        return response([
            'activities'=> $res,
            'message' => 'All activities',
            'status' => 'success'
        ], 201);
    }

    public function searchActivities (Request $request) {
        $text = $request->query('query');
        $activities = Activity::where("user_id", auth()->user()->id)
        ->where('label', 'like', '%'.$text.'%')
        ->get();

        $res = $this->getTotals($activities);

        return response([
            'activities'=> $res,
            'message' => 'Activities results',
            'status' => 'success'
        ], 201);
    }

    public function getSingleActivity ($activityId) {

        $activity = Activity::where("id", $activityId)->first();

        $allUsers = User::all();

        $activity->products;
        $activity->events;
        $activity->company;
        $activity->invoices;
        $activity->comments;
        foreach ($activity->comments as $item) {
            $newArray = [];
            foreach ($allUsers as $item2) {
                if ($item['likersId']) {
                    if (in_array($item2['id'], $item['likersId'])) {
                        $newArray[] = $item2;
                    }
                }
              
            }
            $item->likers = $newArray;
        }

        return response([
            'activity'=> $activity,
            'message' => 'Activity',
            'status' => 'success'
        ], 201);
    }

    public function getActivitiesSummary () {

        $activities = Activity::where("user_id", auth()->user()->id)->get()->toArray();

        $low = array();
        $medium = array();
        $high = array();
        $closed = array();
        for ($i=0; $i<count($activities); $i++) {
            if ($activities[$i]['probability'] === "Low") {
                $low[] = $activities[$i];
            }
            if ($activities[$i]['probability'] === "Medium") {
                $medium[] = $activities[$i];
            }
            if ($activities[$i]['probability'] === "High") {
                $high[] = $activities[$i];
            }
            if ($activities[$i]['probability'] === "Closed") {
                $closed[] = $activities[$i];
            }
        }


        return response([
            'low'=> $low,
            'medium'=> $medium,
            'high'=> $high,
            'closed'=> $closed,
            'status' => 'success'
        ], 201);
    }

    public function updateActivity (Request $request, $activityId) {

        $activity = Activity::where("id", $activityId)->first();

        if (
            ($activity->probability === "High" && $request->probability === "Medium") ||
            ($activity->probability === "Medium" && $request->probability === "Low") ||
            ($activity->probability === "High" && $request->probability === "Low")
        
        ) {
            $activity->decreased_probability = true;
            $activity->save();
        } else if (
            ($activity->probability === "Medium" && $request->probability === "High") ||
            ($activity->probability === "Low" && $request->probability === "Medium") ||
            ($activity->probability === "Low" && $request->probability === "High") 
        ) {
            $activity->decreased_probability = false;
            $activity->save();
        } else if (
            ($activity->probability === "Medium" && $request->probability === "Closed") ||
            ($activity->probability === "Low" && $request->probability === "Closed") ||
            ($activity->probability === "High" && $request->probability === "Closed") 
        ) {
            $activity->decreased_probability = null;
            $activity->save();
        }
        
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

    public function bulkDeleteActivities (Request $request) {

        Activity::whereIn('id', $request->activityIds)->delete();

        return response([
            'message' => 'Activities deleted',
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

        if ($newOwner === null) {
            return response([
                'message' => 'Email does not exit',
                'status' => 'error'
            ], 201);
        }

        $ownActivity = true;

        $transferedActivity = $this->createClone($activity, $ownActivity, $newOwner );

        $res = new Message();
        $res->subject = "Activity Tranfer";
        $res->message =  "Activity ID (".$transferedActivity->id.")";
        $res->receiver_id =  $newOwner->id;
        $res->quill_message = "An activity $transferedActivity->label ($transferedActivity->id) has been transfered to you";

        $res->save();

        return response([
            'message' => 'Activity Transfered',
            'status' => 'success'
        ], 201);
    }

    public function bulkTransfer (Request $request) {
        $request->validate([
            'activityIds'=> 'required',
            'email' => 'required'
        ]);

        $newOwner = User::where("email", $request->email)->first();

        if ($newOwner === null) {
            return response([
                'message' => 'Email does not exit',
                'status' => 'error'
            ], 201);
        }

        foreach ($request->activityIds as $item) {
            $activity = Activity::where("id", $item)->first();
            $ownActivity = true;

            $transferedActivity = $this->createClone($activity, $ownActivity, $newOwner );
    
            $res = new Message();
            $res->subject = "Activity Tranfer";
            $res->message =  "Activity ID (".$transferedActivity->id.")";
            $res->receiver_id =  $newOwner->id;
            $res->quill_message = "An activity $transferedActivity->label ($transferedActivity->id) has been transfered to you";
    
            $res->save();
        }

        
        return response([
            'message' => 'Activities Transfered',
            'status' => 'success'
        ], 201);

    }

    public function dashboardTotalProducts ($owner) {
        if ($owner === "allusers") {
            $activities = Activity::with("invoices")
            ->where("probability", "Closed")
            ->get();
        } else {
            $activities = Activity::with("invoices")
            ->where("user_id", auth()->user()->id)
            ->where("probability", "Closed")
            ->get();
        }
       

        $arr = array();
        $arr_prod = array();

        for ($i=0; $i<count($activities); $i++) {
            $sortedArr = $activities[$i]->invoices->sortBy('created_at')->values();
            if (count($sortedArr)) {
                array_push($arr, $sortedArr[count($sortedArr) - 1]);
            }
           
        }

        for ($j=0; $j<count($arr); $j++) {
           $inv= Invoice::where("id", $arr[$j]->id)->first();
           $month = $inv->created_at->format('F');
           $year = $inv->created_at->format('Y');

           $sum = array_reduce($inv->products->toArray(), function($carry, $item) {
                return $carry + ($item['price'] * $item['pivot']['quantity']);
            });

           $arr_prod[$month."-".$year][] = $sum;
        }

        $res = array();
        foreach($arr_prod as $key=>$value) {
            $sum2 = array_reduce($value, function($carry, $item) {
                return $carry + $item;
             });

            $object = (object) [$key => $sum2];
            $res[] = $object;
        }

        return response([
            "results"=> $res,
            'message' => 'Summary',
            'status' => 'success'
        ], 201);
    }

    public function dashboardTotalSalesUsers () {
        $activities = Activity::with("invoices")
        ->where("probability", "Closed")
        ->get();

        $arr = array();
        $arr_prod = array();

        for ($i=0; $i<count($activities); $i++) {
            $sortedArr = $activities[$i]->invoices->sortBy('created_at')->values();
            // array_push($arr, $sortedArr[count($sortedArr) - 1]);
            if (count($sortedArr)) {
                array_push($arr, $sortedArr[count($sortedArr) - 1]);
            }
        }

        for ($j=0; $j<count($arr); $j++) {
           $inv= Invoice::where("id", $arr[$j]->id)->first();

           $sum = array_reduce($inv->products->toArray(), function($carry, $item) {
                return $carry + ($item['price'] * $item['pivot']['quantity']);
            });

            $user = User::where("id", $inv->user_id)->first();

           $arr_prod[$user->name][] = $sum;
        }

        $res = array();
        foreach($arr_prod as $key=>$value) {
            $sum2 = array_reduce($value, function($carry, $item) {
                return $carry + $item;
             });

            $object = (object) [$key => $sum2];
            $res[] = $object;
        }

        usort($res, function ($a, $b) {
            return end($b) <=> end($a);
        });
        
        // Return the first 5 elements
        $top5 = array_slice($res, 0, 5);

        $newArray = array_map(function($item) {
            return array("name" => key($item), "total" => current($item));
        }, $top5);

        return response([
            "results"=> $newArray,
            'message' => 'Summary',
            'status' => 'success'
        ], 201);
    }

    public function dashboardTotalSalesTopProducts () {
        $activities = Activity::with("invoices")
        ->where("probability", "Closed")
        ->get();

        $arr = array();
        $arr_prod = array();

        for ($i=0; $i<count($activities); $i++) {
            $sortedArr = $activities[$i]->invoices->sortBy('created_at')->values();
            // array_push($arr, $sortedArr[count($sortedArr) - 1]);
            if (count($sortedArr)) {
                array_push($arr, $sortedArr[count($sortedArr) - 1]);
            }
        }

        for ($j=0; $j<count($arr); $j++) {
           //$inv= Invoice::where("id", $arr[$j]->id)->first();

            for ($k=0; $k<count($arr[$j]->products); $k++) {
                $arr_prod[] = $arr[$j]->products[$k];
            }
  
        }

      

        $grouped = array_reduce($arr_prod, function ($result, $item) {
            if (!isset($result[$item['name']])) {
                $result[$item['name']] = array(
                    "name" => $item['name'],
                    "total" => $item['price'] * $item['pivot']['quantity']
                );
            } else {
                $result[$item['name']]['total'] += $item['price'] * $item['pivot']['quantity'];
            }
            return $result;
        }, array());
        
        // sort the array by total value in descending order
        usort($grouped, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        // return first 5 elements
        $firstFive = array_slice($grouped, 0, 5);

        return response([
            "results"=> $firstFive,
            'message' => 'Summary',
            'status' => 'success'
        ], 201);
    }

    public function getActivitiesWithTrashed () {
        $activities = Activity::where("user_id", auth()->user()->id)->withTrashed()->get();

        $res = $this->getTotals($activities);

        foreach ($res as $key => $item) {
            if ($item['deleted_at'] === null) {
                unset($res[$key]);
            }
        }


        return response([
            'activities'=> $res ,
            'message' => 'Activities with Trashed',
            'status' => 'success'
        ], 201);
    }

    public function restoreActivity ($id) {
        $record = Activity::withTrashed()->find($id);

        $record->restore();

        return response([
            'message' => 'Activity restored',
            'status' => 'success'
        ], 201);
    }

    public function forceDeleteActivity ($id) {
        $record = Activity::withTrashed()->find($id);

        $record->forceDelete();

        return response([
            'message' => 'Activity deleted',
            'status' => 'success'
        ], 201);
    }

    public function bulkRestoreActivities (Request $request) {

        Activity::whereIn('id', $request->activityIds)->restore();

        return response([
            'message' => 'Activities restored',
            'status' => 'success'
        ], 201);
    }

    public function bulkForceDeleteActivities (Request $request) {

        Activity::whereIn('id', $request->activityIds)->forceDelete();

        return response([
            'message' => 'Activities deleted',
            'status' => 'success'
        ], 201);
    }
}

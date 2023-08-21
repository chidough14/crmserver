<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class EventController extends Controller
{
    public function createEvent (Request $request) {
        $request->validate([
            'title'=> 'required',
            'start'=> 'required|date',
            'end'=> 'required|date|after:start'
        ]);

        $event = Event::create([
            'title'=> $request->title,
            'description'=> $request->description,
            'user_id'=> auth()->user()->id,
            'activity_id'=> $request->activity_id,
            'start'=> Carbon::parse($request->start)->format('Y-m-d H:i:s'),
            'end'=> Carbon::parse($request->end)->format('Y-m-d H:i:s'),
        ]);

        $event->meeting;

        return response([
            'event'=> $event,
            'message' => 'Event created successfully',
            'status' => 'success'
        ], 201);
    }

    public function dashboardEvents () {

        $events = Event::with('meeting')
        ->where("user_id", auth()->user()->id)
        ->whereDate('start', Carbon::today())
        ->get()->toArray();

        $otherUsersEvents = Event::with('meeting')
        ->where("user_id", "!=",auth()->user()->id)
        ->whereDate('start', Carbon::today())
        ->get();

        $arr = array();
        for ($i=0; $i < count($otherUsersEvents); $i++) {
            if($otherUsersEvents[$i]->meeting) {
                if (in_array(auth()->user()->email, $otherUsersEvents[$i]->meeting->invitedUsers) || $otherUsersEvents[$i]->meeting->meetingType === "Anyone-can-join") {
                    array_push($arr, $otherUsersEvents[$i]);
                }
            }
           
        }

        $response = array_merge($events, $arr);
        
        return response([
            'events'=> $response,
            'message' => 'All events',
            'status' => 'success'
        ], 201);
    }

    public function getEvents () {

        $events = Event::with('meeting')->where("user_id", auth()->user()->id)->get()->toArray();

        $otherUsersEvents = Event::with('meeting')->where("user_id", "!=",auth()->user()->id)->get();

        $arr = array();
        for ($i=0; $i < count($otherUsersEvents); $i++) {
            if($otherUsersEvents[$i]->meeting) {
                if (in_array(auth()->user()->email, $otherUsersEvents[$i]->meeting->invitedUsers) || $otherUsersEvents[$i]->meeting->meetingType === "Anyone-can-join") {
                    array_push($arr, $otherUsersEvents[$i]);
                }
            }
           
        }

        $response = array_merge($events, $arr);

        

        return response([
            'events'=> $response,
            'message' => 'All events',
            'status' => 'success'
        ], 201);
    }

    public function getSingleEvent ($eventId) {

        $event = Event::where("id", $eventId)->first();

        return response([
            'event'=> $event,
            'message' => 'Event',
            'status' => 'success'
        ], 201);
    }

    public function updateEvent (Request $request, $eventId) {

        $event = Event::with("meeting")->where("id", $eventId)->first();

        $request->validate([
            'start'=> 'date',
            'end'=> 'date|after:start'
        ]);

        // $event->update($request->all());

        if ($request->has('start')) {
            $event->start = Carbon::parse($request->start)->format('Y-m-d H:i:s');
        }

        if ($request->has('end')) {
            $event->end = Carbon::parse($request->end)->format('Y-m-d H:i:s');
        }

   
        $event->fill($request->except(['start', 'end']));

        $event->save();

        if($event->meeting !== null) {
            $dateTime = Carbon::parse($event->start);
            $dateOnly = $dateTime->format('m/d/Y');

            $event->meeting->update([
                "meetingDate" => $dateOnly
            ]);
        }

        return response([
            'event'=> $event,
            'message' => 'Event updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteEvent ($eventId) {

        $event = Event::where("id", $eventId)->first();

        $event->delete();

        // For planetscale
        $meeting = Meeting::where("event_id", $eventId)->first();
        
        if($meeting) {
            $meeting->delete();
        }
      

        return response([
            'message' => 'Event deleted',
            'status' => 'success'
        ], 201);
    }

    public function getEventsWithinNextHour () {
        $currentDateTime = date('Y-m-d H:i:s');
        $oneHourLater = strtotime("+2 hour", strtotime($currentDateTime)); // Add one hour

        $newDateTime = date("Y-m-d H:i:s", $oneHourLater); 
        
        $events = Event::whereBetween('start', [$currentDateTime, $newDateTime])
            ->get();
    
        return response([
            "events" => $events,
            "now"=> $currentDateTime,
            "end" => $newDateTime,
            'message' => 'Events',
            'status' => 'success'
        ], 201);
    }
    
    
}

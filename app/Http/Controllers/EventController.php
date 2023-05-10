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

        $event = Event::create($request->all());

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

        $event->update($request->all());

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
        $meeting->delete();

        return response([
            'message' => 'Event deleted',
            'status' => 'success'
        ], 201);
    }
}

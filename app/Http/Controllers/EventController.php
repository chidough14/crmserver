<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function createEvent (Request $request) {
        $request->validate([
            'title'=> 'required',
            'start'=> 'required|date',
            'end'=> 'required|date|after:start'
        ]);

        $event = Event::create($request->all());

        return response([
            'event'=> $event,
            'message' => 'Event created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getEvents () {

        $events = Event::with('meeting')->where("user_id", auth()->user()->id)->get()->toArray();

        $otherUsersEvents = Event::with('meeting')->where("user_id", "!=",auth()->user()->id)->get();

        $arr = array();
        for ($i=0; $i < count($otherUsersEvents); $i++) {
            if($otherUsersEvents[$i]->meeting) {
                if (in_array(auth()->user()->email, $otherUsersEvents[$i]->meeting->invitedUsers)) {
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

        $event = Event::where("id", $eventId)->first();

        $request->validate([
            'start'=> 'date',
            'end'=> 'date|after:start'
        ]);

        $event->update($request->all());

        return response([
            'event'=> $event,
            'message' => 'Event updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteEvent ($eventId) {

        $event = Event::where("id", $eventId)->first();

        $event->delete();

        return response([
            'message' => 'Event deleted',
            'status' => 'success'
        ], 201);
    }
}

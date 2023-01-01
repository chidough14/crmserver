<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function createEvent (Request $request) {
        $request->validate([
            'title'=> 'required',
            'start'=> 'required',
            'end'=> 'required'
        ]);

        $event = Event::create($request->all());

        return response([
            'event'=> $event,
            'message' => 'Event created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getEvents () {

        $events = Event::all();

        return response([
            'events'=> $events,
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

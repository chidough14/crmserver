<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Meeting;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function createMeeting (Request $request) {
        // $request->validate([
        //     'title'=> 'required',
        //     'start'=> 'required|date',
        //     'end'=> 'required|date|after:start'
        // ]);

        $meeting = Meeting::create($request->all());

        return response([
            'meeting'=> $meeting,
            'message' => 'Meeting created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getMeetings () {
        $meetings = Meeting::with('event')
        ->where("user_id", auth()->user()->id)
        ->get()
        ->filter(function($p){
            return $p->event->start >= today();
        })->values();

        $invited = Meeting::with('event')
        ->whereJsonContains('invitedUsers', auth()->user()->email)
        ->get()
        ->filter(function($p){
            return $p->event->start >= today();
        })->values();

        $anyOneCanJoin = Meeting::with('event')
        ->where('meetingType', 'Anyone-can-join')
        ->get()
        ->filter(function($p){
            return $p->event->start >= today();
        })->values();

        $combinedArray = array_merge($invited->toArray(), $anyOneCanJoin->toArray());
        //$meetings->event();

        return response([
            'meetings'=> $meetings,
            'invitedMeetings'=> $combinedArray,
            'message' => 'Meetings',
            'status' => 'success'
        ], 201);
    }

    public function getNotifications () {

        $invited = Meeting::with('event')
        ->whereJsonContains('invitedUsers', auth()->user()->email)
        ->get()
        ->filter(function($p){
            return $p->event->start >= today();
        })->values();

        $inBoxMessages = Message::where("receiver_id", auth()->user()->id)->where("isRead", '=', false)->get();

        $anyOneCanJoin = Meeting::with('event')
        ->where('meetingType', 'Anyone-can-join')
        ->get()
        ->filter(function($p){
            return $p->event->start >= today();
        })->values();

        $combinedArray = array_merge($invited->toArray(), $anyOneCanJoin->toArray());
        //$meetings->event();

        return response([
            'invitedMeetings'=> $combinedArray,
            'inbox'=> $inBoxMessages,
            'message' => 'Meetings',
            'status' => 'success'
        ], 201);
    }
    

    public function updateMeeting (Request $request, $meetingId) {
        $meeting = Meeting::where("id", $meetingId)->first();

        $meeting->update($request->all());

        $event = Event::where("id", $meeting->event_id)->first();
        $start_time = Carbon::parse($event->start);
        $end_time = Carbon::parse($event->end);
        $time_difference_in_minutes = $end_time->diffInMinutes($start_time);

        $event->start = Carbon::parse($request->eventStartDate)->toDateTimeString();
        $event->end = Carbon::parse($request->eventStartDate)->addMinutes($time_difference_in_minutes)->toDateTimeString();
        $event->save();

        $meeting->event;

        return response([
            'meeting'=> $meeting,
            'message' => 'Meetings',
            'status' => 'success'
        ], 201);
    }

    public function getMeetingDetails ($meetingId) {
        $meeting = Meeting::where("meetingId", $meetingId)->first();

        //$meetings->event();

        return response([
            'meeting'=> $meeting,
            'message' => 'Meetings',
            'status' => 'success'
        ], 201);
    }

    public function deleteMeeting ($meetingId) {
        $meeting = Meeting::where("id", $meetingId)->first();

        $event = Event::where("id", $meeting->event_id)->first();

        $event->delete();

        $meeting->delete();



        return response([
            'message' => 'Meeting deleted',
            'status' => 'success'
        ], 201);
    }

    public function bulkDeleteMeeting (Request $request) {
        // Meeting::whereIn('id', $request->meetingIds)->delete();

        foreach ($request->meetingIds as $item) {
            $rec = Meeting::where("id", $item)->first();
            $event = Event::where("id", $rec->event_id)->first();

            $rec->delete();
            $event->delete();
        }

        return response([
            'message' => 'Meetings deleted',
            'status' => 'success'
        ], 201);
    }
}

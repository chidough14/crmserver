<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function getAnnouncements () {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(5);

        return response([
            'announcements'=> $announcements,
            'message' => 'Announcements results',
            'status' => 'success'
        ], 201);

    }

    public function filterAnnouncements (Request $request) {
        $announcements = Announcement::whereIn("category_id", (array) $request->ids)->orderBy('created_at', 'desc')->paginate(5);

        return response([
            'announcements'=> $announcements,
            'message' => 'Announcements results',
            'status' => 'success'
        ], 201);

    }

    public function filterAnnouncementsByDate ( $critera) {
        if ($critera === "1month") {
            $announcements = Announcement::where('created_at', '>', now()->subDays(30)->endOfDay())->paginate(5);
        } 

        if ($critera === "3months") {
            $announcements = Announcement::where('created_at', '>', now()->subDays(60)->endOfDay())->paginate(5);
        } 

        if ($critera === "12months") {
            $announcements = Announcement::where('created_at', '>', now()->subDays(365)->endOfDay())->paginate(5);
        } 

        return response([
            'announcements'=> $announcements,
            'message' => 'Sorted Announcements results',
            'status' => 'success'
        ], 201);

    }

    public function searchAnnouncements (Request $request) {

        $text = $request->query('query');
        $announcements = Announcement::where('message', 'like', '%'.$text.'%')->orderBy('created_at', 'desc')->paginate(5);

        return response([
            'announcements'=> $announcements,
            'message' => 'Announcements results',
            'status' => 'success'
        ], 201);

    }


    public function addAnnouncement (Request $request) {
        if (auth()->user()->role !== "super admin") {
            return response([
                'message' => 'You are nou authorized',
                'status' => 'Unauthorized'
            ], 201);

        } else {
            $request->validate([
                'message'=> 'required',
            ]);
    
            $announcement = Announcement::create([
                'message'=> $request->message,
                'link'=> $request->link,
                'category_id'=> $request->category_id,
            ]);
    
            return response([
                'announcement'=> $announcement,
                'message' => 'Announcement created successfully',
                'status' => 'success'
            ], 201);
        }
       
    }

    public function getAnnouncement ($id) {
        $announcement = Announcement::where('id', $id)->first();
        

        return response([
            'announcement'=> $announcement,
            'message' => 'Announcement result',
            'status' => 'success'
        ], 201);
    }

    public function updateAnnouncement (Request $request, $id) {
        $announcement = Announcement::where('id', $id)->first();

        $announcement->update($request->all());

        return response([
            'announcement'=> $announcement,
            'message' => 'Announcement result',
            'status' => 'success'
        ], 201);
    }

    public function deleteAnnouncement ($id) {
        $announcement = Announcement::where('id', $id)->first();

        $announcement->delete();

        return response([
            'message' => 'Announcement deleted',
            'status' => 'success'
        ], 201);
    }

    public function dashboardAnnouncements () {
        $latestRecords = Announcement::latest('created_at')->take(3)->get();

        return response([
            'announcements'=> $latestRecords,
            'message' => 'Announcements result',
            'status' => 'success'
        ], 201);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CompanyList;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyListController extends Controller
{
    public function createList (Request $request) {
        $request->validate([
            'name'=> 'required',
            'description'=> 'required'
        ]);

        $list = CompanyList::create([
            'name'=> $request->name,
            'description'=> $request->description,
            'type'=> $request->type,
            'user_id'=> $request->user_id,
        ]);

        return response([
            'list'=> $list,
            'message' => 'List created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getAllLists () {

        $lists = CompanyList::where("user_id", auth()->user()->id)->paginate(5);

        return response([
            'lists'=> $lists,
            'message' => 'Lists',
            'status' => 'success'
        ], 201);
    }

    public function getDashboardLists () {

        $lists = CompanyList::where("user_id", auth()->user()->id)->orderBy("created_at", "desc")->get();

        return response([
            'list'=> $lists[0],
            'message' => 'Lists',
            'status' => 'success'
        ], 201);
    }
    

    public function getSingleList ($listId) {

        $list = CompanyList::where('id', $listId)->first();

        $list->companies;

        return response([
            'list'=> $list,
            'message' => 'List',
            'status' => 'success'
        ], 201);
    }

    public function updateList (Request $request, $listId) {

        $list = CompanyList::where('id', $listId)->first();

        $list->update($request->all());

        return response([
            'list'=> $list,
            'message' => 'List updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteList ($listId) {

        $list = CompanyList::where('id', $listId)->first();

        $list->delete();

        return response([
            'message' => 'List deleted',
            'status' => 'success'
        ], 201);
    }

    public function getUserListsAndCompanies () {

        $lists = CompanyList::where('user_id', auth()->user()->id)->with('companies')->get();

        return response([
            'lists'=> $lists,
            'message' => 'Lists with companies',
            'status' => 'success'
        ], 201);
    }

    function createClone ($list, $ownList, $transfer) {
        $list->companies;

        $arr = array();

        for ($i=0; $i < count($list->companies); $i++) {
          $arr[] = $list->companies[$i]->id;
        }

        $clonedList = $list;
        unset($clonedList->id);

        $newList = new CompanyList();
        if ($transfer) {
            $newList->user_id = $transfer->id;
        } else {
            $newList->user_id = ($ownList === true) ? $clonedList->user_id : auth()->user()->id;  
        }
        $newList->name = $clonedList->name."_Copy"; 
        $newList->description = $clonedList->description;
        $newList->type = $clonedList->type; 
        //$newList->user_id = ($ownList === true) ? $clonedList->user_id : auth()->user()->id;  
        $newList->save();

        $newList->companies()->attach($arr);

        return $newList;
    }


    public function cloneList ($listId) {
        
        $list = CompanyList::where("id", $listId)->first();

        if ($list->user_id === auth()->user()->id) {
            $ownList = true;
            $transfer = false;
            $res = $this->createClone($list, $ownList, $transfer);

            return response([
                'clonedList'=> $res,
                'message' => 'List',
                'status' => 'success'
            ], 201);
        } else {
            if ($list->type === "private") {

                return response([
                    'message' => 'Not allowed',
                    'status' => 'success'
                ], 201);

            } else {
                $ownList = false;
                $transfer = false;
                $response = $this->createClone($list, $ownList, $transfer);

                return response([
                    'clonedList'=> $response,
                    'message' => 'List',
                    'status' => 'success'
                ], 201);
            }
        }
    }

    public function transferList (Request $request, $listId) {
        $list = CompanyList::where("id", $listId)->first();

        $newOwner = User::where("email", $request->email)->first();
        
        if ($newOwner === null) {
            return response([
                'message' => 'Email does not exit',
                'status' => 'error'
            ], 201);
        }

        $ownList = true;

        $transferedList = $this->createClone($list, $ownList, $newOwner );

        $res = new Message();
        $res->subject = "List Tranfer";
        $res->message = "A list $transferedList->name ($transferedList->id) has been transfered to you";
        $res->receiver_id =  $newOwner->id;

        $res->save();

        return response([
            'message' => 'List Transfered',
            'status' => 'success'
        ], 201);
    }
}

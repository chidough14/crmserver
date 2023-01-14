<?php

namespace App\Http\Controllers;

use App\Models\CompanyList;
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

        $lists = CompanyList::where("user_id", auth()->user()->id)->get();

        return response([
            'lists'=> $lists,
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

    function createClone ($list, $ownList) {
        $list->companies;

        $arr = array();

        for ($i=0; $i < count($list->companies); $i++) {
          $arr[] = $list->companies[$i]->id;
        }

        $clonedList = $list;
        unset($clonedList->id);

        $newList = new CompanyList();
        $newList->name = $clonedList->name."_Copy"; 
        $newList->description = $clonedList->description;
        $newList->type = $clonedList->type; 
        $newList->user_id = ($ownList === true) ? $clonedList->user_id : auth()->user()->id;  
        $newList->save();

        $newList->companies()->attach($arr);

        return $newList;
    }


    public function cloneList ($listId) {
        
        $list = CompanyList::where("id", $listId)->first();

        if ($list->user_id === auth()->user()->id) {
            $ownList = true;
            $res = $this->createClone($list, $ownList);

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
                $response = $this->createClone($list, $ownList);

                return response([
                    'clonedList'=> $response,
                    'message' => 'List',
                    'status' => 'success'
                ], 201);
            }
        }
    }
}

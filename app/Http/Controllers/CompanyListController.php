<?php

namespace App\Http\Controllers;

use App\Models\CompanyList;
use Illuminate\Http\Request;

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

        $lists = CompanyList::all();

        return response([
            'lists'=> $lists,
            'message' => 'Lists',
            'status' => 'success'
        ], 201);
    }

    public function getSingleList ($listId) {

        $list = CompanyList::where('id', $listId)->first();

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
}

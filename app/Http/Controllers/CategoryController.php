<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategories () {
        $categories = Category::all();

        return response([
            'categories'=> $categories,
            'message' => 'Categories results',
            'status' => 'success'
        ], 201);
    }

    public function addCategory (Request $request) {
        if (auth()->user()->role !== "super admin") {
            return response([
                'message' => 'You are nou authorized',
                'status' => 'Unauthorized'
            ], 201);

        } else {
            $request->validate([
                'name'=> 'required',
            ]);
    
            $category = Category::create([
                'name'=> $request->name
            ]);
    
            return response([
                'category'=> $category,
                'message' => 'Category created successfully',
                'status' => 'success'
            ], 201);
        }
    }

    public function getCategory ($id) {
        $category = Category::where('id', $id)->first();
        

        return response([
            'category'=> $category,
            'message' => 'Category result',
            'status' => 'success'
        ], 201);
    }

    public function updateCategory (Request $request, $id) {
        $category = Category::where('id', $id)->first();

        $category->update($request->all());
        

        return response([
            'category'=> $category,
            'message' => 'Category updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteCategory ($id) {
        $category = Category::where('id', $id)->first();

        $category->delete();
        

        return response([
            'message' => 'Category deleted',
            'status' => 'success'
        ], 201);
    }

    public function bulkAddCategory (Request $request) {
        $newRecords = collect($request->categoriesPayload)->map(function ($item) {
            return Category::create($item);
        });
        
        return response([
            'categories' => $newRecords,
            'message' => 'Categories added',
            'status' => 'success'
        ], 201);
    }
}

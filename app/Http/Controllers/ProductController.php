<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function createProduct (Request $request) {
        $request->validate([
            'name'=> 'required',
            'price'=> 'required|between:0,99.99',
            'tax_percentage'=> 'between:0,99.99'
        ]);

        $product = Product::create($request->all());

        return response([
            'product'=> $product,
            'message' => 'Product created successfully',
            'status' => 'success'
        ], 201);
    }


    public function getProductsNoPagination () {
        $products = Product::all();

        return response([
            'products'=> $products,
            'message' => 'All Products',
            'status' => 'success'
        ], 201);
    }

    public function getProducts () {
        $products = Product::paginate(5);

        return response([
            'products'=> $products,
            'message' => 'All Products',
            'status' => 'success'
        ], 201);
    }

    public function getSingleProduct ($productId) {

        $product = Product::where("id", $productId)->first();

        return response([
            'product'=> $product,
            'message' => 'Product',
            'status' => 'success'
        ], 201);
    }

    public function updateProduct (Request $request, $productId) {
        $request->validate([
            'price'=> 'between:0,99.99',
            'tax_percentage'=> 'between:0,99.99'
        ]);

        $product = Product::where("id", $productId)->first();

        $product->update($request->all());

        return response([
            'product'=> $product,
            'message' => 'Product updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteProduct ($productId) {

        $product = Product::where("id", $productId)->first();

        $product->delete();

        return response([
            'message' => 'Product deleted',
            'status' => 'success'
        ], 201);
    }
}

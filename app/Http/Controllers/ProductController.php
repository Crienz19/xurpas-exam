<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function order(ProductRequest $request)
    {
        $request->validated();
        
        // getting the selected item from the database
        $selectedProduct = Product::where('id', $request->product_id)->first();

        // checking if the product is existing if not it will fire a 404 response
        if (!empty($selectedProduct)) {
            
            $isInsufficientStock = $selectedProduct->available_stock < $request->quantity;
            
            // checking if has enough stock to order 
            if ($isInsufficientStock) {
                return response()->json([
                    'message' => 'Failed to order this product due to unavailability of the stock'
                ], 400);
            }

            $newQuantity = (int) $selectedProduct->available_stock - (int) $request->quantity;

            Product::where('id', $request->product_id)
                ->update([
                    'available_stock'   =>  $newQuantity
                ]);
            
            Order::create([
                'user_id'   =>  $request->user()->id,
                'product_id'=>  $request->product_id,
                'quantity'  =>  $request->quantity
            ]);

            // response message with status code 201 or CREATED
            return response()->json([
                'message' => 'You have successfully ordered this product.'
            ], 201);
        }

        // response message with status code 404 or NOT FOUND
        return response()->json([
            'message'   =>  'No product'
        ], 404);
    }
}

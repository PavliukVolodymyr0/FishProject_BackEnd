<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Ordered_products;
use App\Models\Goods;
use App\Models\Category;
use App\Models\Special_offers;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show_products(Request $request)
    {   
        if(isset($request->category_id)){
        $products=Goods::where('category_id', $request->category_id)->get();}
        else{
            $products=Goods::all();
        }
        return response()->json(['products' => $products], 201);
    }

    public function show_categories()
    {   
        $categories=Category::all();
        return response()->json(['categories' => $categories], 201);
    }

    public function show_orders()
    {   
        $orders=Orders::with('orderedProducts')->get();
       // orders::with('address', 'orderedProducts')->findOrFail($request->id);
        return response()->json(['orders' => $orders], 201);
    }

    public function show_special_offers()
    {   
        $special_offers=Special_offers::all();
        return response()->json(['special_offers' => $special_offers], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function order(Request $request)
    {

        $order= orders::create([
            'surname' =>$request->surname,
            'name' => $request->name,
            'patronymic' => $request->patronimic,
            'phone_number' => $request->phone_number,
            'city' => $request->city,
            'street' => $request->street,
            'house' => $request->house,
            'status' => 1,
            'payment_type' => $request->payment_type,
            'total_price' => 0,
            'send_type' => $request->send_type,
        ]);

        $order_id=$order->getKey();
        $order_exemple=$request->order;
        $total_price=0;
        foreach ($order_exemple as $ordered_pr) {
            $ordered_product = Ordered_products::create([
            'order_id' =>$order_id,
            'product_id' => $ordered_pr['id'],
            'quantity' => $ordered_pr['quantity'],
            ]);
            $product = Goods::findOrFail($ordered_pr['id']);
            $total_price=$total_price+$product->price*$ordered_pr['quantity'];
        }
       $order->total_price=$total_price;
        $order->save();

        return response()->json(['message' => $total_price], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_order(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

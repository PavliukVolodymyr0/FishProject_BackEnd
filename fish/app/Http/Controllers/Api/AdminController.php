<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sensors;
use App\Models\Category;
use App\Models\Goods;
use App\Models\Orders;

class AdminController extends Controller
{
    public function add_category(Request $request)
    {
        $path = "no photo";
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('category', 'public');}
        $category=Category::create([
            'name' =>$request->name,
            'photo' => $path,
        ]);
        if($path =="no photo"){
            return response()->json(['message' => 'Photo not found'], 200);}
        return response()->json(['message' => 'Success'], 200);
    }

    public function add_product(Request $request)
    {
        $path = "no photo";
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('product', 'public');}
        $category=Goods::create([
            'name' =>$request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'photo' => $path,
        ]);
        if($path=="no photo"){return response()->json(['message' => 'Photo not found'], 200);}
        return response()->json(['message' => 'Success'], 200);
    }

    public function sensor(Request $request)
{
    $start_date = mktime(0, 0, 0, $request->month, $request->day, $request->year);
    $end_date = mktime(23, 59, 59, $request->month, $request->day, $request->year);

    switch ($request->timeline) {
        case 1:
            // For 1 day timeline, end date remains the same as start date
            break;
        case 7:
            $end_date += (7 * 24 * 60 * 60); // Add 7 days in seconds
            break;
        case 30:
            $end_date = mktime(0, 0, 0, $request->month + 1, $request->day, $request->year);
            break;
        default:
            // Handle invalid timeline value
            break;
    }

    $sensors_array = Sensors::where('name', $request->sensor)
        ->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
        ->where('created_at', '<', date('Y-m-d H:i:s', $end_date))
        ->get();

    return response()->json($sensors_array);
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
        $order=Orders::findOrFail($request->id);
        $order->status=$request->status;
        $order->save();
        return response()->json(['message' => "success"], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

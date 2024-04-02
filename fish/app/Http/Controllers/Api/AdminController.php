<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sensors;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

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

    public function showWarnings()
    {
        $criticalwater = 140; // Поріг критичного значення (для прикладу)
        $criticaltemp = 25; // Поріг критичного значення (для прикладу)
        $criticalacidity = 8; // Поріг критичного значення (для прикладу)
        $criticaloxygen = 50; // Поріг критичного значення (для прикладу)

        $warnings = [];

        // Отримати останні записи з бази даних для кожного типу показника
        $latestWaterLevel = Sensors::where('name', 'water_level')->latest('created_at')->first();
        $latestTemperature = Sensors::where('name', 'temperature')->latest('created_at')->first();
        $latestAcidity = Sensors::where('name', 'acidity')->latest('created_at')->first();
        $latestOxygenLevel = Sensors::where('name', 'oxygen_level')->latest('created_at')->first();

        // Перевірити критичні показники
        if ($latestWaterLevel && $latestWaterLevel->value < $criticalwater) {
            $warnings[] = 'Низький рівень води!';
        }

        if ($latestTemperature && $latestTemperature->value > $criticaltemp) {
            $warnings[] = 'Висока температура!';
        }

        if ($latestAcidity && $latestAcidity->value > $criticalacidity) {
            $warnings[] = 'Висока кислотність!';
        }

        if ($latestOxygenLevel && $latestOxygenLevel->value < $criticaloxygen) {
            $warnings[] = 'Низький рівень кисню!';
        }

        if (empty($warnings)) {
            return response()->json(['message' => 'Всі показники в нормі.']);
        } else {
            return response()->json(['warnings' => $warnings]);
        }
    }

    public function addSensor(Request $request)
    {
        // Перевірка вхідних даних
        $validatedData = $request->validate([
            'name' => 'required|string',
            'value' => 'required|numeric',
        ]);

        // Створення нового запису
        $sensor = Sensors::create([
        'name' => $request->name,
        'value' => $request->value
        ]);

        // Повернення відповіді
        return response()->json(['message' => 'Датчик успішно додано'], 201);
    }
}

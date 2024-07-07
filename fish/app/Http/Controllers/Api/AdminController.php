<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sensors;
use App\Models\Category;
use App\Models\Goods;
use App\Models\Special_offers;
use App\Models\Orders;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
        $product=Goods::create([
            'name' =>$request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'photo' => $path,
        ]);
        
        if(isset($request->special_price)){
            $special_offer=Special_offers::create([
                'product_id' => $product->id,
                'special_price' => $request->special_price,
            ]);
        }

        if($path=="no photo"){return response()->json(['message' => 'Photo not found'], 200);}
        return response()->json(['message' => 'Success'], 200);
    }

    public function update_product(Request $request)
{
    $path = "no photo";
   
    $product = Goods::findOrFail($request->product_id);

    if ($request->has('name')) {
        $product->name = $request->name;
    }

    if ($request->has('category_id')) {
        $product->category_id = $request->category_id;
    }

    if ($request->has('price')) {
        $product->price = $request->price;
    }

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('product', 'public');
        $product->photo = $path;
    }

    $product->save();

    if ($request->has('special_price')) {
        $special_offer = Special_offers::updateOrCreate(
            ['product_id' => $product->id],
            ['special_price' => $request->special_price]
        );
    } else {
        $special_offer = Special_offers::where('product_id', $product->id)->first();
        if ($special_offer) {
            $special_offer->delete();
        }
    }

    if ($path == "no photo") {
        return response()->json(['message' => 'Photo not found'], 200);
    }
    
    return response()->json(['message' => 'Success'], 200);
}

    public function getDates(Request $request)
    {
        $range = $request->input('range');

        // Отримання унікальних дат з таблиці sensors
        $dates = Sensors::selectRaw('DATE(created_at) as date')
            ->distinct()
            ->pluck('date')
            ->toArray();

        $formattedDates = [];

        switch ($range) {
            case 'day':
                foreach ($dates as $date) {
                    $formattedDates[] = Carbon::parse($date)->format('d.m.Y');
                }
                break;

            case 'week':
                $weeks = [];
                foreach ($dates as $date) {
                    $startOfWeek = Carbon::parse($date)->startOfWeek()->format('d.m.Y');
                    $endOfWeek = Carbon::parse($date)->endOfWeek()->format('d.m.Y');
                    $weekRange = $startOfWeek . '-' . $endOfWeek;
                    if (!in_array($weekRange, $weeks)) {
                        $weeks[] = $weekRange;
                    }
                }
                $formattedDates = $weeks;
                break;

            case 'month':
                $months = [];
                foreach ($dates as $date) {
                    $month = Carbon::parse($date)->format('m.Y');
                    if (!in_array($month, $months)) {
                        $months[] = $month;
                    }
                }
                $formattedDates = $months;
                break;
        }
        $formattedDates = array_reverse($formattedDates);

        return response()->json(['dates' => $formattedDates], 200);
    }


    public function getSensorData(Request $request)
{
    $dateRange = $request->input('date_range');
    $range = $request->input('range');
    if (strpos($dateRange, '-') !== false) {
        list($start, $end) = explode('-', $dateRange);

        $startDate = Carbon::parse(trim($start));

    } else {
        if (preg_match('/^\d{2}\.\d{4}$/', trim($dateRange))) {
            // Якщо так, додаємо перший день місяця та перетворюємо в Carbon
            $startDate = Carbon::createFromFormat('d.m.Y', '01.' . trim($dateRange));
            $endDate = $startDate->copy()->endOfMonth();
        } else {
            $startDate = Carbon::parse(trim($dateRange));
            $endDate = $startDate->copy()->endOfDay();
        }
    }

    $sensorData = [];

    switch ($range) {
        case 'day':
            $sensorData = Sensors::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();
    
        // Отримуємо дані о півночі наступного дня
        $nextDay = $endDate->copy()->addDay();
        $midnightData = Sensors::whereIn('name', ['oxygen_level', 'temperature', 'acidity', 'water_level'])
            ->where('created_at', '=', $nextDay->format('Y-m-d 00:00:00'))
            ->orderBy('created_at')
            ->get();
    
        $groupedData = $sensorData->groupBy('name')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'value' => round($item->value, 2), // Округлення до двох знаків після коми
                    'created_at' => $item->created_at->format('H:i') // Форматуємо час вимірювання
                ];
            });
        });
    
        if ($midnightData->isNotEmpty()) {
            foreach ($midnightData as $data) {
                $groupedData[$data->name][] = [
                    'value' => round($data->value, 2), // Округлення до двох знаків після коми
                    'created_at' => $data->created_at->format('H:i') // Форматуємо час вимірювання
                ];
            }
        }

        $formattedData = [];
        foreach ($groupedData as $name => $data) {
            $formattedData[] = [
                'name' => $name,
                'data' => $data
            ];
        }
        $sensorData = $formattedData;
            break;

        case 'week':
            $data = [];

            // Цикл по кожному дню у вибраному тижні
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // Отримання всіх показників за поточний день
                $dailyData = Sensors::whereIn('name', ['oxygen_level', 'temperature', 'acidity', 'water_level'])
                    ->whereDate('created_at', '=', $date->format('Y-m-d'))
                    ->orderBy('created_at')
                    ->get();
        
                // Усереднення значень для кожного датчика
                $averagedData = $dailyData->groupBy('name')->map(function ($items) {
                    $averageValue = $items->avg('value');
                    return round($averageValue, 2); // Округлення до двох знаків після коми
                });
        
                // Формування одного підмасиву з усіма значеннями за день
                $dayData = [
                    'oxygen_level' => $averagedData['oxygen_level'] ?? null,
                    'temperature' => $averagedData['temperature'] ?? null,
                    'acidity' => $averagedData['acidity'] ?? null,
                    'water_level' => $averagedData['water_level'] ?? null,
                    'day_of_week' => $date->format('d'), // Номер дня тижня
                ];
        
                // Додавання даних за день до загального масиву даних
                $data[] = $dayData;
            }
            
            $sensorData = $data;
            break;

        case 'month':
            $endDate = Carbon::parse($startDate)->endOfMonth();
    $data = [];
    $date = $startDate->copy();

    while ($date->lte($endDate)) {
        $dailyData = Sensors::whereDate('created_at', '=', $date->format('Y-m-d'))
            ->orderBy('name')
            ->get();

        $averagedData = $dailyData->groupBy('name')->map(function ($item) {
            $averageValue = $item->avg('value');
            return round($averageValue, 2); // Округлення до 2 знаків після коми
        });

        // Додамо дату у форматі "день місяця"
        $dayOfMonth = $date->format('d');
        $averagedData['day_of_month'] = $dayOfMonth;

        $data[] = $averagedData;

        $date->addDay();
    }   
            $sensorData = $data;
            break;

        default:
            return response()->json(['error' => 'Invalid range specified'], 400);
    }

    return response()->json(['sensor_data' => $sensorData], 200);
}

    /*public function sensor(Request $request)
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
}*/


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

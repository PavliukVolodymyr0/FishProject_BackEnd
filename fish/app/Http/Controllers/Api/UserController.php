<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\users;

class UserController extends Controller
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
    public function store(Request $request)
    {
        //
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

    public function login(Request $request)
    {
        // Валідація введених даних
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Пошук користувача за email
        $user = users::where('login', $request->email)->first();

        // Перевірка чи користувач існує та чи вірний пароль
        if ($request->password==$user->password) {
            // Авторизація пройшла успішно
            return response()->json(['message' => 'Login successful'], 200);
        } else {
            // Помилка авторизації
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}

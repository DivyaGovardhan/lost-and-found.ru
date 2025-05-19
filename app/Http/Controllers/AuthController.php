<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:100|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'birthday' => 'nullable|date',
        ]);

        $user = User::create([
            'login' => $request->login,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'birthday' => $request->birthday,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)->firstOrFail();

        // Проверка блокировки
        if ($user && $user->is_blocked) {
            return response()->json([
                'message' => 'Your account has been blocked due to multiple policy violations'
            ], 403);
        }
    
        if (!Auth::attempt($request->only('login', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
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
            'birthday' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'contacts' => 'required|array|min:1',
            'contacts.*' => 'required|string|max:255',
        ]);

        $user = User::create([
            'login' => $request->login,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'birthday' => $request->birthday,
        ]);

        // Создаём контакты
        foreach ($request->contacts as $contact) {
            $user->contacts()->create(['contact' => $contact]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('contacts'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)->first();

        if ($user && $user->is_blocked) {
            \Log::info('Blocked login attempt', [
                'user_id' => $user->id,
                'login' => $request->login
            ]);

            return response()->json([
                'message' => 'Ваш аккаунт заблокирован за нарушения'
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

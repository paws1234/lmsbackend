<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle an incoming login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'role' => $user->role,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * Handle an incoming logout request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete(); 
        }

        return response()->json(['message' => 'Successfully logged out']);
    }
}
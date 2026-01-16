<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $validated['name'], 
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'] ?? 'student',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'user'    => $user,
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        //  dd($request);       
        // $validated = $request->validate([
        //     'email'    => 'required|email',
        //     'password' => 'required|string',
        // ]);


        $user = User::where('email', $request['email'])->first();
        if (! $user || ! Hash::check($request['password'], $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Optional: remove old tokens
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully',
        ]);
    }
}



    

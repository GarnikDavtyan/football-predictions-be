<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        Auth::login($user);

        return $this->successResponse([
            'access_token' => $token,
            'user' => $user,
        ], 'Registered successfully', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('LibraryAppToken')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'user' => $user,
            ], 'Logged in successfully');
        }

        return $this->errorResponse('Invalid login credentials.', 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Logged out');
    }
}

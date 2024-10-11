<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Jobs\SendVerificationEmail;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $accessToken = $user->createToken('predictor-user-token')->plainTextToken;
        Auth::login($user);

        $refreshToken = Str::random(60);
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshToken
        ]);

        SendVerificationEmail::dispatch($user);

        return $this->successResponse([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => $user,
        ], 'Registered successfully', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $accessToken = $user->createToken('predictor-user-token')->plainTextToken;

            $refreshToken = Str::random(60);
            RefreshToken::updateOrCreate([
                'user_id' => $user->id
            ], [
                'token' => $refreshToken
            ]);

            return $this->successResponse([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'user' => $user,
            ], 'Logged in successfully', 201);
        }

        return $this->errorResponse('Invalid login credentials.', 401);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->tokens()->delete();
        $user->refreshToken()->delete();

        return $this->successResponse(null, 'Logged out');
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $refreshToken = $request->refresh_token;

        if (!$refreshToken) {
            return $this->errorResponse('Refresh token not found', 401);
        }

        $refreshToken = RefreshToken::where('token', $refreshToken)->first();

        if (!$refreshToken) {
            return $this->errorResponse('Invalid refresh token', 401);
        }

        $user = $refreshToken->user;
        $accessToken = $user->createToken('predictor-user-token')->plainTextToken;

        $newRefreshToken = Str::random(60);
        $refreshToken->token = $newRefreshToken;
        $refreshToken->save();

        return $this->successResponse([
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'user' => $user,
        ], 'Token refreshed', 201);
    }
}

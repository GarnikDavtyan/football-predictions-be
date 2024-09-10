<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetLinkRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Jobs\SendPasswordResetLinkEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(PasswordResetLinkRequest $request): JsonResponse
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->errorResponse('There is no user with that email.', 400);
        }

        if (!$user->hasVerifiedEmail()) {
            return $this->errorResponse('The email is not verified. Cannot reset the password', 422);
        }

        SendPasswordResetLinkEmail::dispatch($email);

        return $this->successResponse([], 'Password reset email has been sent.');
    }

    public function reset(PasswordResetRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->all(),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->errorResponse('Error resetting password. Please try again.');
        }

        return $this->successResponse([], 'New password saved successfully.');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verifyEmail(int $id, string $hash)
    {
        $user = User::findOrFail($id);
        $frontendUrl = config('app.frontend_url');

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect($frontendUrl . '/verification/invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect($frontendUrl . '/verification/already-verified');
        }

        $user->markEmailAsVerified();

        return redirect($frontendUrl . '/verification/verified');
    }

    public function resendMail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse([], 'The email is already verified');
        }

        SendVerificationEmail::dispatch($user);

        return $this->successResponse([], 'The verification email has been sent');
    }
}

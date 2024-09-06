<?php

namespace App\Http\Controllers;

use App\Jobs\SendVerificationEmail;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verifyEmail(int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(config('app.frontend_url') . '/verification/invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(config('app.frontend_url') . '/verification/already-verified');
        }

        $user->markEmailAsVerified();

        return redirect(config('app.frontend_url') . '/verification/verified');
    }

    public function resendMail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse([], 'The email is already verified');
        }

        SendVerificationEmail::dispatch($user);

        return $this->successResponse([], 'The verification email has been sent');
    }
}

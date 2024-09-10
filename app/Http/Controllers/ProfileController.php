<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Mail\DeleteAccount;
use App\Models\DeleteAccountToken;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            $user = User::findOrFail(Auth::id());

            $avatarPath = '';
            $oldAvatarPath = '';

            if ($request->hasFile('avatar')) {
                $avatarPath = Storage::putFile('avatars', $request->file('avatar'));
                $data['avatar'] = $avatarPath;

                $oldAvatarPath = $user->avatar;
            }

            if ($request->old_password && $request->new_password) {
                if (!Hash::check($request->old_password, $user->password)) {
                    throw new Exception('The old password is incorrect');
                }
                $data['password'] = Hash::make($request->new_password);
            }

            $user->update($data);

            if ($oldAvatarPath) {
                Storage::delete($oldAvatarPath);
            }

            return $this->successResponse($user, 'The profile updated successfully');
        } catch (Exception $e) {
            if ($avatarPath) {
                Storage::delete($avatarPath);
            }

            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteAvatar()
    {
        try {
            $user = User::findOrFail(Auth::id());
            $avatarPath = $user->avatar;

            if ($avatarPath) {
                $user->avatar = null;
                $user->save();

                Storage::delete($avatarPath);
            }

            return $this->successResponse([], 'Avatar deleted');
        } catch (Exception $e) {
            return $this->errorResponse();
        }
    }

    public function requestDeleteAccount()
    {
        $user = Auth::user();
        $token = Str::random(60);

        DeleteAccountToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(30),
        ]);

        Mail::to($user)->queue(new DeleteAccount($user, $token));

        return $this->successResponse([], 'A confirmation email has been sent to your email address.');
    }

    public function deleteAccount(int $id, string $token)
    {
        $frontendUrl = config('app.frontend_url');

        $deleteToken = DeleteAccountToken::where('token', hash('sha256', $token))
            ->where('user_id', $id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$deleteToken) {
            return redirect($frontendUrl . '/delete-account/invalid');
        }

        $user = User::findOrFail($id);
        $avatarPath = $user->avatar;

        try {
            DB::beginTransaction();

            $user->points()->delete();
            $user->leaguePoints()->delete();
            $user->roundPoints()->delete();
            $user->predictions()->delete();
            $user->tokens()->delete();

            $user->delete();

            DB::commit();

            if ($avatarPath) {
                Storage::delete($avatarPath);
            }

            return redirect($frontendUrl . '/delete-account/successful');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect($frontendUrl . '/delete-account/error');
        }
    }
}

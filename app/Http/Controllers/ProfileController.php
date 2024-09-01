<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
}

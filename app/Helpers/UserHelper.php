<?php

namespace App\Helpers;

use Laravel\Sanctum\PersonalAccessToken;

class UserHelper
{
    /**
     * Auth:id() is not working when calling outside of auth:sanctum middleware.
     * Getting user id from access token
     */
    public static function getAuthUserId(string $token): ?int
    {
        $accessToken = PersonalAccessToken::findToken($token);
        $authId = $accessToken ? $accessToken->tokenable_id : null;

        return $authId;
    }
}


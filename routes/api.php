<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\FixturesController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(LeagueController::class)->group(function () {
    Route::get('/leagues', 'index');
    Route::get('/leagues/{leagueId}/standings', 'getStandings');
});

Route::get('/fixtures/{leagueId}/{round}', [FixturesController::class, 'getFixtures']);

Route::controller(PointsController::class)->group(function () {
    Route::get('/points/{leagueId}/{round}', 'getLeagueTop');
    Route::get('/points', 'getTop');
});

Route::middleware(['guest'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('refresh-token', 'refreshToken');
    });

    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verifyEmail'])
        ->middleware('signed')->name('verification.verify');

    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::post('/password/email', 'sendResetLinkEmail')->middleware('throttle:6,1');
        Route::post('/password/reset', 'reset');
    });

    Route::get('/profile/account-delete/{id}/{token}', [ProfileController::class, 'deleteAccount']);
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/fixtures/{leagueId}/{round}', [FixturesController::class, 'savePredictions']);

    Route::controller(ProfileController::class)->group(function () {
        Route::put('/profile', 'updateProfile');
        Route::delete('/profile/avatar-delete', 'deleteAvatar');
        Route::post('/profile/account-delete-request', 'requestDeleteAccount')
            ->middleware('throttle:6,1');
    });

    Route::get('user', function () {
        return Auth::user();
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/email/resend-verification', [VerificationController::class, 'resendMail'])
        ->middleware('throttle:6,1')->name('verification.send');

    Route::post('/logout', [AuthController::class, 'logout']);
});

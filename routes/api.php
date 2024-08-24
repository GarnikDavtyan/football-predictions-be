<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FixturesController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\PointsController;
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

Route::get('leagues', [LeagueController::class, 'index']);
Route::get('fixtures/{leagueId}/{round}', [FixturesController::class, 'getFixtures']);
Route::get('leagues/{leagueId}/standings', [LeagueController::class, 'getStandings']);
Route::get('points/{leagueId}/{round}', [PointsController::class, 'getLeagueTop']);
Route::get('points', [PointsController::class, 'getTop']);

Route::middleware(['guest'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('fixtures/{leagueId}/{round}', [FixturesController::class, 'savePredictions']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
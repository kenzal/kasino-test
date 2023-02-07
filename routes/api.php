<?php

use App\Http\Resources\GameCollection;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/games', function (Request $request) {
    return new GameCollection(Game::query()->orderByDesc('created_at')->with(['user'])->paginate(3));
});  // All Games
Route::get('/games/my/', function (Request $request) {});  // My Games
Route::get('/games/{game}', function (Request $request, Game $game) {return $game->refresh()->toResource();})->name('game');
Route::post('/play/dice', function (Request $request) {});
Route::post('/play/gems', function (Request $request) {});
Route::get('/seeds', function (Request $request) {});
Route::post('/seeds', function (Request $request) {});




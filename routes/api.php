<?php

use App\Exceptions\Games\GameImmutableException;
use App\Http\Controllers\CurrencyController;
use App\Http\Requests\Games;
use App\Http\Requests\Games\BlackjackRequest;
use App\Http\Requests\Games\GemsRequest;
use App\Http\Resources\GameCollection;
use App\Models\Game;
use App\Models\Games\Blackjack;
use App\Models\Games\Dice;
use App\Models\Games\Gems;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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
Auth::loginUsingId(2);
Route::middleware('auth:sanctum')->get('/user',
    function (Request $request) {
        return $request->user();
    });
Route::resource('currency', CurrencyController::class)
     ->only(['index', 'show']);

Route::get('/games',
    function (Request $request) {
        return new GameCollection(Game::query()->orderByDesc('created_at')->with(['user'])->paginate());
    })->name('allGames');  // All Games
Route::get('/games/my/',
    function (Request $request) {
    })->name('allMyGames');  // My Games
Route::get('/games/{game}',
    function (Request $request, Game $game) {
        return $game->refresh()->toResource();
    })->name('game');
Route::post('/games/{game}/{action}',
    function (Request $request, Game $game, string $action) {
        if ($game->isCompleted) {
            throw new GameImmutableException;
        }
        if (!in_array($action, $game->getActions())) {
            throw new BadRequestException;
        }
        $method = 'action'.ucfirst($action);
        $game->$method();
        return $game->refresh()
                    ->toResource();
    })->name('gameAction');
Route::post('/play/dice',
    function (Games\DiceRequest $request) {
        /** @var User $user */
        $user = $request->user();
        $game = match (true) {
            (bool) $request->target            => Dice::newFromTarget(
                user     : $user,
                currency : $request->currency,
                amount   : $request->amount,
                target   : $request->target,
                direction: $request->direction),
            (bool) $request->target_multiplier => Dice::newFromMultiplier(
                user      : $user,
                currency  : $request->currency,
                amount    : $request->amount,
                multiplier: $request->target_multiplier,
                direction : $request->direction),
            (bool) $request->win_chance        => Dice::newFromWinChance(
                user     : $user,
                currency : $request->currency,
                amount   : $request->amount,
                winChance: $request->win_chance,
                direction: $request->direction),
        };
        $game->play();

        return $game->refresh()
                    ->toResource()
                    ->response()
                    ->header('Location', route('game', $game));
    });
Route::post('/play/gems',
    function (GemsRequest $request) {
        /** @var User $user */
        $user = $request->user();
        $game = new Gems(['amount' => $request->amount]);
        $game->user()->associate($user);
        $game->currency()->associate($request->currency);
        $game->play();

        return $game->refresh()
                    ->toResource()
                    ->response()
                    ->header('Location', route('game', $game));

    });
Route::post('/play/blackjack',
    function (BlackjackRequest $request) {
        /** @var User $user */
        $user = $request->user();
        $game = new Blackjack(['amount' => $request->amount]);
        $game->user()->associate($user);
        $game->currency()->associate($request->currency);
        $game->play();

        return $game->refresh()
                    ->toResource()
                    ->response()
                    ->header('Location', route('game', $game));

    });
Route::get('/seeds',
    function (Request $request) {
    });
Route::post('/seeds',
    function (Request $request) {
    });

Route::get('/test',
    function (Request $request) {
    $data = Round::find(37)->result;
    $obj = new \App\Values\Games\Blackjack\RoundResult($data);
    return $obj;
    });


<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Games\Blackjack;
use Illuminate\Http\Request;
use Inertia\Response;

class BlackjackController extends Controller
{
    public function index(Request $request, Blackjack $game): Response {
        return inertia('Games/Blackjack/Index', [
            'game'=>$game,
        ]);
    }

    public function continue(Request $request, Blackjack $game): Response {
        inertia()->modal('Games/Blackjack/PlayModal');
        return $this->index($request, $game);
    }
}

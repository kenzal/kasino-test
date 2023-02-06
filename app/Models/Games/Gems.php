<?php

namespace App\Models\Games;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gems extends Game
{
    protected $table = 'games_gems';
    use HasFactory;
}

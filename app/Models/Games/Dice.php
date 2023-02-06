<?php

namespace App\Models\Games;

use App\Enums\OverUnder;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property OverUnder $direction
 * @property string    $target
 * @property string    $target_multiplier
 * @property string    $win_chance
 */
class Dice extends Game
{
    protected $table = 'games_dice';
    use HasFactory;

    protected $casts = [
        'direction' => OverUnder::class,
    ];

    protected $fillable = [
        'amount',
        'direction',
        'result',
        'target',
        'target_multiplier',
        'win_chance',
    ];
}

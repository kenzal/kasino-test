<?php

namespace App\Models\Games;

use App\Enums\OverUnder;
use App\Models\Games;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dice extends Games
{
    protected $table = 'games_dice';
    use HasFactory;

    protected $casts = [
        'direction'=>OverUnder::class,
    ];

    protected $fillable = [
        'nonce',
        'amount',
        'result',
        'target',
        'target_multiplier',
        'win_chance',
        'direction',
    ];
}

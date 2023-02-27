<?php

namespace App\Support\Collections;

use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameCollection extends Collection
{
    public function forfeitAll(): self
    {
        return $this->each(fn(Game $game) => $game->forfeit());
    }
}

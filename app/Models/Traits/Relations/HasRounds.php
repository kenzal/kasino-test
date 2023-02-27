<?php

namespace App\Models\Traits\Relations;

use App\Models\Round;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property Round              $lastRound;
 * @property Round[]|Collection $rounds
 */
trait HasRounds
{

    protected ?string $roundResultClass = null;

    public function lastRound(): HasOne|Round
    {
        $relation = $this->hasOne(Round::class, 'game_id')
                         ->ofMany('game_round');
        if ($this->roundResultClass) {
            $relation->withCasts(['result' => $this->roundResultClass]);
        }
        return $relation;
    }

    /**
     * @return HasMany|Round[]
     */
    public function rounds(): HasMany|array
    {
        $relation = $this->hasMany(Round::class, 'game_id')->orderBy('game_round');
        if ($this->roundResultClass) {
            $relation->withCasts(['result' => $this->roundResultClass]);
        }
        return $relation;
    }
}

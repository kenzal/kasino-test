<?php

namespace App\Models\Games;

use App\Enums\OverUnder;
use App\Exceptions\Games\GameImmutableException;
use App\Models\Currency;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\QueryException;

/**
 * @property OverUnder $direction
 * @property string    $lucky_number
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
        'user_id',
        'currency_id',
    ];

    public static function houseEdge(): string
    {
        return env('DICE_HOUSE_EDGE', env('HOUSE_EDGE', '1.00'));
    }

    public static function playerEdge(): string
    {
        return bcdiv(bcsub(100, self::houseEdge()), 100, 4);
    }

    public static function multiplierMax(): string
    {
        $minWinChance = "0.01";
        return bcmul(bcdiv(100, $minWinChance), self::playerEdge(), 2);
    }

    public static function multiplierMin(): string
    {
        $maxWinChance = "99.99";
        return bcmul(bcdiv(100, $maxWinChance), self::playerEdge(), 2);
    }

    public static function newFromTarget(
        User         $user,
        Currency     $currency,
        string|float $amount,
        string|float $target,
        OverUnder    $direction
    ): self {
        $winChance  = $direction == OverUnder::UNDER ? $target : 100 - $target;
        $multiplier = 100 / $winChance * self::playerEdge();
        return new self(
            [
                'amount'            => $amount,
                'currency_id'       => $currency->id,
                'direction'         => $direction,
                'target'            => $target,
                'user_id'           => $user->id,
                'target_multiplier' => $multiplier,
                'win_chance'        => $winChance,
            ]);
    }

    public static function newFromMultiplier(
        User         $user,
        Currency     $currency,
        string|float $amount,
        string|float $multiplier,
        OverUnder    $direction
    ): self {
        $winChance = bcdiv(bcmul(self::playerEdge(), 100), $multiplier, 2);
        $target    = $direction == OverUnder::UNDER ? $winChance : 100 - $winChance;
        return new self(
            [
                'amount'            => $amount,
                'currency_id'       => $currency->id,
                'direction'         => $direction,
                'target'            => $target,
                'user_id'           => $user->id,
                'target_multiplier' => $multiplier,
                'win_chance'        => $winChance,
            ]);
    }

    public static function newFromWinChance(
        User         $user,
        Currency     $currency,
        string|float $amount,
        string|float $winChance,
        OverUnder    $direction
    ): self {
        $target     = $direction == OverUnder::UNDER ? $winChance : 100 - $winChance;
        $multiplier = 100 / $winChance * self::playerEdge();
        return new self(
            [
                'amount'            => $amount,
                'currency_id'       => $currency->id,
                'direction'         => $direction,
                'target'            => $target,
                'user_id'           => $user->id,
                'target_multiplier' => $multiplier,
                'win_chance'        => $winChance,
            ]);
    }

    /**
     * @throws GameImmutableException
     */
    public function play(): Dice
    {
        while (!$this->exists) {
            $this->user->unsetRelation('currentSeed');
            $this->seed_id = $this->user->currentSeed->id;
            if($this->isDirty('seed_id')) $this->load('seed');
            $this->refreshNonce();
            $hash = $this->getHash();
            $num = 0;
            bcscale(20);
            $num = bcadd($num, bcdiv(hexdec(substr($hash,6,2)), bcpow(256,1)));
            $num = bcadd($num, bcdiv(hexdec(substr($hash,4,2)), bcpow(256,2)));
            $num = bcadd($num, bcdiv(hexdec(substr($hash,2,2)), bcpow(256,3)));
            $num = bcadd($num, bcdiv(hexdec(substr($hash,0,2)), bcpow(256,4)));

            $this->lucky_number = bcmul($num, '100.01',2);

            $isWinner = $this->direction == OverUnder::UNDER ? $this->lucky_number < $this->target : $this->lucky_number>$this->target;
            bcscale(0);
            $this->result = bcmul((int)$isWinner, bcmul($this->amount, $this->target_multiplier));
            try {
                $this->save();
            } catch (QueryException $e) {
                //try again
            }
        }
        return $this;
    }
}

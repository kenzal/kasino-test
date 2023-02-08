<?php

namespace App\Models\Games;

use App\Enums\Games\Gems\Gem as GemEnum;
use App\Models\Game;
use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\QueryException;

class Gems extends Game
{
    protected $table = 'games_gems';
    use HasFactory;


    public function play(): Gems
    {
        $this->save();
        while (!$this->rounds()->count()) {
            /** @var Round $round */
            $round = $this->rounds()->make(
                [
                    'game_id'    => $this->id,
                    'game_round' => 0,
                ]);

            $this->user->unsetRelation('currentSeed');
            $round->seed_id = $this->user->currentSeed->id;
            $round->refreshNonce();
            $hash = $round->getHash();
            bcscale(20);
            $symbols = [];
            foreach (range(0, 4) as $place) {
                $num = 0;
                foreach (range(0, 3) as $byte) {
                    $num = bcadd($num,
                                 bcdiv(hexdec(substr($hash, $place * 8 + (3 - $byte) * 2, 2)), bcpow(256, $byte + 1)));
                }
                $symbols[$place] = bcmul($num, '7', 0);
            }
            $round->result = $symbols;
            bcscale(0);
            $this->result = bcmul($this->getMultiplier($symbols), $this->amount);
            try {
                $round->save();
                $this->completed_at = $round->refresh()->created_at;
                $this->save();
            } catch (QueryException $e) {
                //try again
            }
        }
        return $this;
    }

    private function getMultiplier(array $symbols): float
    {
        $counts       = array_count_values($symbols);
        rsort($counts);
        return match (count($counts)) {
            1 => 50,                     // Five of a Kind
            2 => $counts[0]==4 ? 5 : 4,  // Four of a Kind or Full House
            3 => $counts[0]==3 ? 3 : 2,  // Three of a Kind or Two Pair
            4 => 0.10,                   // One Pair
            5 => 0,                      // No Matches
        };
    }
}

<?php

namespace App\Models\Games;

use App\Enums\Games\Razzle\RollResultType;
use App\Exceptions\Games\GameImmutableException;
use App\Models\Game;
use App\Models\RazzleBoard;
use App\Models\Round;
use App\Values\Games\Razzle\RoundResult;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use OutOfRangeException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;


/**
 * @property RazzleBoard $board;
 * @property string      $prize;
 */
class Razzle extends Game
{

    // region Metadata
    protected         $table            = 'games_razzle';
    protected ?string $roundResultClass = RoundResult::class;

    public function getActions(): array
    {
        return ['play', 'reset'];
    }

    /**
     * @throws GameImmutableException
     */
    public function play(): Razzle
    {
        $this->prize = bcmul(config('games.razzle.initial'), $this->amount, 0);
        $this->save();
        while (!$this->rounds()->count()) {
            /** @var Round $round */
            $round = $this->rounds()->make(
                [
                    'game_id'    => $this->id,
                    'game_round' => 0,
                    'result'     => new RoundResult,
                ]);

            $this->refreshRound($round);
            $this->board()->associate(RazzleBoard::fromSeed(substr(hash('sha256', $round->seed->client_seed), -32)));
            $roundResult               = $round->result;
            $roundResult->currentWager = $this->amount;
            $round->result             = $roundResult;
            $roundResult               = $this->processRound($round);
            try {
                $round->result = $roundResult;
                if ($this->isCompleted) {
                    $round->created_at = $this->completed_at;
                }
                $round->save();
                $this->save();
            } catch (QueryException $e) {
                //try again
            }
        }
        return $this;
    }

    //endregion

    public function board(): BelongsTo
    {
        return $this->belongsTo(RazzleBoard::class, 'razzle_board_seed');
    }

    //region Actions

    /**
     * @param  Round  $round
     *
     * @return RoundResult
     * @throws UnknownProperties
     */
    protected function processRound(Round $round): RoundResult
    {
        /** @var RoundResult $roundResult */
        $roundResult                 = is_array($round->result) ? new RoundResult($round->result) : $round->result;
        $roundResult->cups           = $this->rollCups($round);
        $roundResult->values         = $this->board->getValues($roundResult->cups);
        $roundResult->rollResultType = $this->lookupType($roundResult->calculateCupSum());
        $roundResult->rollResult     = $this->lookupResult($roundResult->cupSum);
        switch ($roundResult->rollResultType) {
            case RollResultType::DOUBLER:
                $roundResult->currentWager = bcmul(2, $roundResult->currentWager, 0);
                $this->prize               = bcmul(2, $this->prize);
                break;
            case RollResultType::MULTIPLIER:
                $this->payOut(bcmul($roundResult->rollResult, $roundResult->currentWager));
                break;
            case RollResultType::NOTHING:
                break;
            case RollResultType::POINTS:
                $roundResult->totalPoints += $roundResult->rollResult;
                if ($roundResult->totalPoints >= 100) {
                    $this->payOut($this->prize);
                    $this->completed_at = now();
                }
                break;
        }
        return $roundResult;
    }

    /**
     * @param  Round  $round
     *
     * @return int[]
     */
    protected function rollCups(Round $round): array
    {
        $hash     = $this->getHash($round);
        $cups     = [];
        $oldScale = bcscale(20);
        foreach (range(0, 8) as $place) {
            $num = 0;
            foreach (range(0, 3) as $byte) {
                $num = bcadd($num,
                             bcdiv(hexdec(substr($hash, $place * 8 + (3 - $byte) * 2, 2)), bcpow(256, $byte + 1)));
            }
            $cup = (int) bcmul($num, '180', 0);
            while (in_array($cup, $cups)) {
                $cup = ($cup + 1) % 180;
            }
            $cups[] = $cup;
        }
        bcscale($oldScale);
        return $cups;
    }


    public function getHash(Round $round): string
    {
        return hash('sha256',
                    implode(':',
                            [
                                $round->seed->server_seed,
                                $round->seed->client_seed,
                                $round->game_round,
                                $round->nonce,
                            ]
                    )
        );
    }

    //endregion

    protected function lookupType(int $cupSum): RollResultType
    {
        return match (true) {
            $cupSum == 29                  => RollResultType::DOUBLER,
            $cupSum >= 20 && $cupSum <= 36 => RollResultType::NOTHING,
            $cupSum >= 18 && $cupSum <= 38 => RollResultType::MULTIPLIER,
            $cupSum >= 8 && $cupSum <= 48  => RollResultType::POINTS,
            default                        => throw new OutOfRangeException('Value must be between 8 and 48 (inclusive)'),
        };
    }

    protected function lookupResult(int $cupSum)
    {
        return match (true) {
            $cupSum >= 20 && $cupSum <= 36 => null,
            $cupSum >= 18 && $cupSum <= 38 => config('games.razzle.multiplier'),
            in_array($cupSum, [17, 39])    => 5,
            in_array($cupSum, [16, 40])    => 10,
            in_array($cupSum, [15, 41])    => 15,
            in_array($cupSum, [14, 42])    => 20,
            in_array($cupSum, [11, 45])    => 30,
            $cupSum >= 10 && $cupSum <= 36 => 50,
            $cupSum >= 8 && $cupSum <= 48  => 100,
            default                        => throw new OutOfRangeException('Value must be between 8 and 48 (inclusive)'),
        };
    }

    /**
     * @throws UnknownProperties
     */
    public function actionPlay(): self
    {
        $this->refresh();
        $lastRound = $this->lastRound;
        /** @var RoundResult $roundResult */
        $round                    = $lastRound->replicate(['seed_id', 'nonce', 'game_round']);
        $round->game_round        = $lastRound->game_round + 1;
        $round->previous_round_id = $lastRound->id;
        $roundResult              = is_array($round->result) ? new RoundResult($round->result) : $round->result;
        $this->increaseWager($roundResult->currentWager);
        $round->result = $roundResult;
        while (!$round->exists) {
            $this->refreshRound($round);
            $roundResult = $this->processRound($round);
            try {
                $round->result = $roundResult;
                if ($this->isCompleted) {
                    $round->created_at = $this->completed_at;
                }
                $round->save();
                $this->save();
            } catch (QueryException $e) {
                //try again
            }
        }

        return $this;
    }

    public function actionReset(Round $round = null): self
    {
        $this->completed_at = now();
        $this->result       += 0;

        return $this;
    }

}

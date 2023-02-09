<?php

namespace App\Models\Games;

use App\Enums\Cards\Card;
use App\Enums\Cards\Rank;
use App\Exceptions\Games\GameImmutableException;
use App\Exceptions\Games\InvalidGameAction;
use App\Models\Game;
use App\Models\Round;
use App\Values\Games\Blackjack\Hand;
use App\Values\Games\Blackjack\RoundResult;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;

/**
 * @property Round $lastRound;
 */
class Blackjack extends Game
{
    // region Metadata
    protected $table = 'games_blackjack';

    /**
     * @return HasMany
     */
    public function rounds(): HasMany|array
    {
        return parent::rounds()->withCasts(['result' => RoundResult::class]);
    }

    public function lastRound(): HasOne|Round
    {
        return $this->hasOne(Round::class, 'game_id')
                    ->ofMany('game_round')
                    ->withCasts(['result' => RoundResult::class]);
    }

    public function getActions(): array
    {
        $latest = $this->lastRound;
        return $latest ? $latest->result->actions : [];
    }

    //endregion

    public function play(): Blackjack
    {
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
            $cards         = $this->drawCards(round: $round, count: 4);
            $dealerCards   = [$cards[1], $cards[3]];
            $dealerHand    = new Hand(hand: $dealerCards);
            $playerHand    = new Hand(hand: [$cards[0], $cards[2]], wager: $this->amount, currency: $this->currency);
            $roundResult   = (new RoundResult)->setDealer($dealerHand)->setHands([$playerHand]);
            $askInsurance  = $this->askInsurance($dealerHand);
            $playerNatural = $playerHand->isNatural();
            if ($askInsurance) {
                if ($playerNatural) {
                    $roundResult->actions = ['evenMoneyYes', 'evenMoneyNo'];
                } else {
                    $roundResult->actions = ['insuranceYes', 'insuranceNo'];
                }
            } else {
                if ($playerNatural) {
                    if ($dealerHand->isNatural()) {
                        // Push
                        $this->result = $this->amount;
                    } else {
                        //Blackjack!
                        $this->result = bcdiv(bcmul(3, $this->amount), 2, 0);
                    }
                    $this->completed_at = now();
                } else {
                    if ($this->dealerShows10($dealerHand) && $dealerHand->isNatural()) {
                        $this->result       = 0;
                        $this->completed_at = now();
                    } else {
                        $roundResult->actions = ['hit', 'stand', 'double'];
                        if ($playerHand->canSplit()) {
                            $roundResult->actions[] = 'split';
                        }
                    }
                }
            }
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

    //region Actions
    public function actionHit(): self
    {
        return $this;
    }

    public function actionStand(): self
    {
        return $this;
    }

    public function actionDouble(): self
    {
        return $this;
    }

    /**
     * @throws GameImmutableException
     * @throws InvalidGameAction
     */
    public function actionSplit(): self
    {
        $lastRound = $this->lastRound;
        /** @var RoundResult $roundResult */
        $roundResult              = $lastRound->result;
        $round                    = $lastRound->replicate(['seed_id', 'nonce', 'game_round']);
        $round->game_round        = $lastRound->game_round + 1;
        $round->previous_round_id = $lastRound->id;
        $activeHand               = $roundResult->hands[$roundResult->active_hand];
        if (!$activeHand->canSplit()) {
            throw new InvalidGameAction;
        }
        $this->increaseWager($activeHand->wager);
        while (!$round->exists) {
            $this->refreshRound($round);
            $cards    = $this->drawCards(round: $round, count: 2);
            $newHands = [];
            foreach (range(0, 1) as $i) {
                $newHands[$i] = new Hand(wager   : $activeHand->wager,
                                         currency: $activeHand->currency,
                                         hand    : [$activeHand->hand[$i], $cards[$i]]);
            }
            array_splice($roundResult->hands, $roundResult->active_hand, 1, $newHands);
            $roundResult->actions = ['hit', 'stand', 'double'];
            $activeHand           = $roundResult->hands[$roundResult->active_hand];
            if ($activeHand->canSplit()) {
                $roundResult->actions[] = 'split';
            }
            try {
                $round->result = $roundResult;
                $round->save();
                $this->save();
            } catch (QueryException $e) {
                dd($e);
            }
        }
        return $this;
    }

    /**
     * @throws InvalidGameAction
     */
    public function actionEvenMoneyYes(): self
    {
        return $this->actionInsuranceYes();
    }

    /**
     * @throws GameImmutableException
     * @throws InvalidGameAction
     */
    public function actionInsuranceYes(): self
    {
        if ($this->rounds()->count() != 1) {
            throw new InvalidGameAction;
        }
        $round = new Round;
        $round->game()->associate($this);
        $round->previousRound()->associate($this->rounds()->first());
        $round->result     = $round->previousRound->result;
        $round->game_round = 1;
        $originalAmount    = $this->amount;
        bcscale(0);
        $insurance                  = bcdiv($originalAmount, 2);
        $round->result['insurance'] = $insurance;
        $this->increaseWager($insurance);
        while (!$round->exists) {
            $this->refreshRound($round);

            if ($round->previousRound->result['dealer']->isNatural()) {
                $this->result       = bcmul($insurance, 2);
                $this->completed_at = now();
            } else {
                $round->result['actions'] = ['hit', 'stand', 'double'];
                if ($round->result['hands'][0]->canSplit()) {
                    $round->result['actions'][] = 'split';
                }
            }
            try {
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

    /**
     * @throws InvalidGameAction
     */
    public function actionEvenMoneyNo(): self
    {
        return $this->actionInsuranceNo();
    }

    public function actionInsuranceNo(): self
    {
        if ($this->rounds()->count() != 1) {
            throw new InvalidGameAction;
        }
        $round = new Round;
        $round->game()->associate($this);
        $round->previousRound()->associate($this->rounds()->first());
        $round->result     = $round->previousRound->result;
        $round->game_round = 1;
        bcscale(0);
        while (!$round->exists) {
            $this->refreshRound($round);

            if ($round->previousRound->result['dealer']->isNatural()) {
                $this->result       = 0;
                $this->completed_at = now();
            } else {
                $round->result['actions'] = ['hit', 'stand', 'double'];
                if ($round->result['hands'][0]->canSplit()) {
                    $round->result['actions'][] = 'split';
                }
            }
            try {
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

    //region HelperMethods
    /**
     * @param  Round  $round
     *
     * @return void
     * @throws GameImmutableException
     */
    protected function refreshRound(Round $round): void
    {
        $this->user->unsetRelation('currentSeed');
        $round->seed_id = $this->user->currentSeed->id;
        $round->refreshNonce();
    }


    /**
     * @param  Card[]  $dealerCards
     *
     * @return bool
     */
    protected function askInsurance(Hand $dealerHand): bool
    {
        return $dealerHand->hand[0]->rank() == Rank::ACE;
    }

    protected function dealerShows10(Hand $dealerHand): bool
    {
        return self::getCardValue($dealerHand->hand[0]) == 10;
    }

    public static function getCardValue(Card $card): ?int
    {
        $rank = $card->rank();
        return match ($rank->value) {
            1              => null,
            10, 11, 12, 13 => 10,
            default        => $rank->value
        };
    }

    /**
     * @param  Round  $round
     * @param  int    $count
     * @param  bool   $fresh
     *
     * @return int[]
     */
    private function drawCards(Round $round, int $count, bool $fresh = false): array
    {
        static $start = 0;
        if ($fresh) {
            $start = 0;
        }
        $hash = $latestHash = $round->getHash();
        while (strlen($hash) < 8 * ($start + $count)) {
            $hash .= ($latestHash = hash('sha256', implode(':', [$round->seed->server_seed, $latestHash])));
        }
        $oldScale = bcscale(20);
        $cards    = [];
        foreach (range($start, ($start + $count - 1)) as $place) {
            $num = 0;
            foreach (range(0, 3) as $byte) {
                $num = bcadd($num,
                             bcdiv(hexdec(substr($hash, $place * 8 + (3 - $byte) * 2, 2)), bcpow(256, $byte + 1)));
            }
            $cards[] = (int) bcmul($num, '52', 0);
        }
        $start += count($cards);
        bcscale($oldScale);
        return $cards;
    }
    //endregion

}

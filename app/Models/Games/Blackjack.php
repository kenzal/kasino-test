<?php

namespace App\Models\Games;

use App\Enums\Cards\Card;
use App\Enums\Cards\Rank;
use App\Exceptions\Games\GameImmutableException;
use App\Exceptions\Games\InvalidGameAction;
use App\Exceptions\Games\UnexpectedHashChangeException;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Values\Games\Blackjack\Hand;
use App\Values\Games\Blackjack\RoundResult;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @property Round $lastRound;
 */
class Blackjack extends Game
{

    // region Metadata
    protected         $table            = 'games_blackjack';
    protected ?string $roundResultClass = RoundResult::class;

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
                        if ($playerHand->canSplit() && $roundResult->hasRoomToSplit()) {
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

    /**
     * @throws UnknownProperties
     * @throws GameImmutableException
     * @throws InvalidGameAction
     */
    public function actionHit(): self
    {
        $lastRound = $this->lastRound;
        /** @var RoundResult $roundResult */
        $round                    = $lastRound->replicate(['seed_id', 'nonce', 'game_round']);
        $round->game_round        = $lastRound->game_round + 1;
        $round->previous_round_id = $lastRound->id;
        $roundResult              = is_array($round->result) ? new RoundResult($round->result) : $round->result;
        $activeHand               = $roundResult->hands[$roundResult->active_hand];
        while (!$round->exists) {
            $this->refreshRound($round);
            $activeHand[]                                  = Card::from($this->drawCards(round:$round, startFrom: $roundResult->totalCards())[0]);
            $roundResult->hands[$roundResult->active_hand] = $activeHand;
            $round->result                                 = $roundResult;
            try {
                if ($activeHand->standValue() >= 21 || (count($activeHand) == config('games.blackjack.charlie'))) {
                    $this->actionStand($round);
                } else {
                    $roundResult->actions = ['hit', 'stand'];
                    $round->result        = $roundResult;
                }
                if ($this->isCompleted) {
                    $round->created_at = $this->completed_at;
                }
                $round->save();
                $this->save();
            } catch (QueryException|UnexpectedHashChangeException $e) {
                //try again
            }
        }
        return $this;
    }

    /**
     * @throws UnknownProperties
     * @throws GameImmutableException
     * @throws UnexpectedHashChangeException
     */
    public function actionStand(Round $round = null): self
    {
        $existingHash = null;
        if ($round) {
            $existingHash = $round->getHash();
        } else {
            $lastRound = $this->lastRound;
            /** @var RoundResult $roundResult */
            $round                    = $lastRound->replicate(['seed_id', 'nonce', 'game_round']);
            $round->game_round        = $lastRound->game_round + 1;
            $round->previous_round_id = $lastRound->id;
        }
        /** @var RoundResult $roundResult */
        $roundResult          = is_array($round->result) ? new RoundResult($round->result) : $round->result;
        $roundResult->actions = [];
        do {
            $roundResult->active_hand++;
            $activeHand = ($roundResult->active_hand < count($roundResult->hands)) ? $roundResult->hands[$roundResult->active_hand] : null;
        } while ($activeHand && $activeHand->value() == 21);
        while (!$round->exists) {
            $this->refreshRound($round);
            if ($existingHash and $existingHash != $round->getHash()) {
                throw new UnexpectedHashChangeException;
            }
            if ($roundResult->active_hand == count($roundResult->hands)) {
                $round->result = $roundResult;
                if (array_filter($roundResult->hands, fn(Hand $hand) => $hand->standValue() <= 21)) {
                    $this->resolveDealer($round);
                }
                $this->completed_at = now();
                $roundResult        = is_array($round->result) ? new RoundResult($round->result) : $round->result;
                $this->result       = $roundResult->getWinningAmount();
            } else {
                // On a fresh (2-card) active hand that does not value 21 (Decision Time!)
                $roundResult->actions = ['hit', 'stand', 'double'];
                if ($activeHand->canSplit() && $roundResult->hasRoomToSplit()) {
                    $roundResult->actions[] = 'split';
                }
            }
            $round->result = $roundResult;
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
     * @throws UnknownProperties
     * @throws InvalidGameAction
     */
    public function actionDouble(): self
    {
        $lastRound = $this->lastRound;
        /** @var RoundResult $roundResult */
        $round                    = $lastRound->replicate(['seed_id', 'nonce', 'game_round']);
        $round->game_round        = $lastRound->game_round + 1;
        $round->previous_round_id = $lastRound->id;
        $roundResult              = is_array($round->result) ? new RoundResult($round->result) : $round->result;
        $activeHand               = $roundResult->hands[$roundResult->active_hand];
        if (count($activeHand) != 2) {
            throw new InvalidGameAction;
        }
        $this->increaseWager($activeHand->wager);
        $activeHand->wager = bcmul($activeHand->wager, 2, 0);
        while (!$round->exists) {
            $this->refreshRound($round);
            $activeHand[]                                  = Card::from($this->drawCards($round)[0]);
            $roundResult->hands[$roundResult->active_hand] = $activeHand;
            $round->result                                 = $roundResult;
            try {
                $this->actionStand($round);
                if ($this->isCompleted) {
                    $round->created_at = $this->completed_at;
                }
                $round->save();
                $this->save();
            } catch (QueryException|UnexpectedHashChangeException $e) {
                //try again
            }
        }

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
        if (!$activeHand->canSplit() && $roundResult->hasRoomToSplit()) {
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
                                         hand    : [$activeHand[$i], $cards[$i]]);
            }
            array_splice($roundResult->hands, $roundResult->active_hand, 1, $newHands);
            $activeHand           = $roundResult->hands[$roundResult->active_hand];
            if ($activeHand->isNatural()) {
                do {
                    $roundResult->active_hand++;
                    $activeHand = ($roundResult->active_hand < count($roundResult->hands)) ? $roundResult->hands[$roundResult->active_hand] : null;
                } while ($activeHand && $activeHand->value() == 21);
            }
            if ($roundResult->active_hand == count($roundResult->hands)) {
                $round->result = $roundResult;
                if (array_filter($roundResult->hands, fn(Hand $hand) => $hand->standValue() <= 21)) {
                    $this->resolveDealer($round);
                }
                $this->completed_at = now();
                $roundResult        = is_array($round->result) ? new RoundResult($round->result) : $round->result;
                $this->result       = $roundResult->getWinningAmount();
            } else {
                $roundResult->actions = ['hit', 'stand', 'double'];
                if ($activeHand->canSplit() && $roundResult->hasRoomToSplit()) {
                    $roundResult->actions[] = 'split';
                }
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
     * @throws UnknownProperties
     */
    public function actionInsuranceYes(): self
    {
        if ($this->rounds()->count() != 1) {
            throw new InvalidGameAction;
        }
        $round = new Round;
        $round->game()->associate($this);
        $round->previousRound()->associate($this->rounds()->first());
        $roundResult = $round->previousRound->result instanceof RoundResult
            ? $round->previousRound->result
            : new RoundResult($round->previousRound->result);

        $round->game_round = 1;
        $originalAmount    = $this->amount;
        bcscale(0);
        $insurance = bcdiv($originalAmount, 2);

        $roundResult->insurance = $insurance;
        $this->increaseWager($insurance);
        while (!$round->exists) {
            $this->refreshRound($round);

            if ($roundResult->dealer->isNatural()) {
                $this->result       = $roundResult->hands[0]->isNatural() ? $originalAmount : bcmul($insurance, 2);
                $this->completed_at = now();
            } elseif ($roundResult->hands[0]->isNatural()) {
                $this->result       = bcdiv(bcmul(3, $this->amount), 2, 0);
                $this->completed_at = now();
            } else {
                $roundResult->actions = ['hit', 'stand', 'double'];
                if ($roundResult->hands[0]->canSplit() && $roundResult->hasRoomToSplit()) {
                    $roundResult->actions[] = 'split';
                }
            }
            $round->result = $roundResult;
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

    /**
     * @throws UnknownProperties
     * @throws GameImmutableException
     * @throws InvalidGameAction
     */
    public function actionInsuranceNo(): self
    {
        if ($this->rounds()->count() != 1) {
            throw new InvalidGameAction;
        }
        $round = new Round;
        $round->game()->associate($this);
        $round->previousRound()->associate($this->rounds()->first());
        $roundResult = $round->previousRound->result instanceof RoundResult
            ? $round->previousRound->result
            : new RoundResult($round->previousRound->result);

        $round->game_round = 1;
        bcscale(0);
        while (!$round->exists) {
            $this->refreshRound($round);

            if ($round->previousRound->result->dealer->isNatural()) {
                $this->result       = 0;
                $this->completed_at = now();
            } else {
                $roundResult->actions = ['hit', 'stand', 'double'];
                if ($roundResult->hands[0]->canSplit() && $roundResult->hasRoomToSplit()) {
                    $roundResult->actions[] = 'split';
                }
            }
            $round->result = $roundResult;
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
        return $dealerHand[0]->rank() == Rank::ACE;
    }

    protected function dealerShows10(Hand $dealerHand): bool
    {
        return self::getCardValue($dealerHand[0]) == 10;
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
     * @throws UnknownProperties
     */
    protected function resolveDealer(Round $round)
    {
        $roundResult = is_array($round->result) ? new RoundResult($round->result) : $round->result;
        while ($roundResult->dealer->standValue() < 17) {
            $roundResult->dealer[] = Card::from($this->drawCards($round)[0]);
        }
        $round->result = $roundResult;
    }

    public function getHash(Round $round): string
    {
        return hash('sha256',
                    implode(':',
                            [
                                $round->seed->server_seed,
                                $round->seed->client_seed,
                                0,
                                $round->nonce,
                            ]
                    )
        );
    }

    /**
     * @param  Round      $round
     * @param  int        $count
     * @param  int|null   $startFrom
     *
     * @return int[]
     */
    private function drawCards(Round $round, int $count = 1, int $startFrom = null): array
    {
        static $start = 0;
        if (!is_null($startFrom)) {
            $start = $startFrom;
        }
        $hash = $latestHash = $this->getHash($round);
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

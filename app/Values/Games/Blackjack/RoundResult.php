<?php

namespace App\Values\Games\Blackjack;

use App\Casters\Games\Blackjack\HandArrayCaster;
use App\Casters\Games\Blackjack\HandCaster;
use JessArcher\CastableDataTransferObject\CastableDataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

class RoundResult extends CastableDataTransferObject
{
    /**
     * @var string[]
     */
    public array $actions=[];

    #[CastWith(HandCaster::class)]
    public ?Hand $dealer;

    /**
     * @var Hand[]
     */
    #[CastWith(HandArrayCaster::class)]
    public array $hands=[];
    public int $active_hand = 0;
    public ?string $insurance;


    public function setDealer(Hand $dealerHand): self
    {
        $this->dealer = $dealerHand;
        return $this;
    }

    /**
     * @param  Hand[]  $playerHands
     *
     * @return $this
     */
    public function setHands(array $playerHands): self
    {
        $this->hands = $playerHands;
        return $this;
    }

    public function getWinningAmount(): string
    {
        $winnings = "0";
        $dealerValue = $this->dealer->standValue();
        if($dealerValue > 21) {
            foreach($this->hands as $hand) {
                if($hand->standValue() <= 21) $winnings = bcadd($winnings, bcmul(2,$hand->wager));
            }
        } else {
            foreach ($this->hands as $hand) {
                $standValue = $hand->standValue();
                if ($standValue > 21) {
                    continue;
                }
                if ($standValue == $dealerValue)
                    $winnings = bcadd($winnings, $hand->wager);
                elseif ($hand->meetsCharlie() || $standValue > $dealerValue) {
                    $winnings = bcadd($winnings, bcmul(2,$hand->wager));
                }
            }
        }
        return $winnings;
    }

    public function hasRoomToSplit(): bool
    {
        $maxHands = config('games.blackjack.max_hands');
        if (!$maxHands) return true;
        return (count($this->hands) < $maxHands);
    }

    public function allBusted(): bool
    {
        foreach($this->hands as $hand) {
            if(!$hand->isBust()) return false;
        }
        return true;
    }

    public function totalCards(): int
    {
        return count($this->dealer) + array_sum(array_map(fn(Hand $hand)=>count($hand),$this->hands));
    }
}

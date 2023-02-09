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
}

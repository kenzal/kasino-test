<?php

namespace App\Values\Games\Blackjack;

use App\Casters\Games\Blackjack\HandCaster;
use App\Enums\Cards\Card;
use App\Enums\Cards\Rank;
use App\Models\Currency;
use App\Models\Games\Blackjack;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;

#[CastWith(HandCaster::class)]
class Hand extends DataTransferObject implements JsonSerializable, Arrayable
{
    public ?Currency $currency = null;
    /**
     * @var ?Card[]
     */
    public array   $hand  = [];
    public ?string $wager = null;

    public function __construct(...$args)
    {
        parent::__construct($args);
        foreach ($this->hand as &$card) {
            $card = is_int($card) ? Card::from($card) : $card;
        }
    }

    public function canSplit(): bool
    {
        if (count($this->hand) != 2) {
            return false;
        }

        if(config('games.blackjack.split_on_value', false))
            return Blackjack::getCardValue($this->hand[0]) === Blackjack::getCardValue($this->hand[1]);
        return $this->hand[0]->rank() === $this->hand[1]->rank();
    }

    public function hasHiddenCards():bool
    {
        return in_array(null, $this->hand);
    }

    public function value():int|array
    {
        //Filter out hidden cards
        $hand = array_filter($this->hand);

        //Aces value as null and sort to the front
        $values = array_map(fn(Card $card) => Blackjack::getCardValue($card), $hand);
        sort($values);

        //Consecutive Aces always value at 1
        foreach ($values as $key => $value) {
            if ($key && !$value) {
                $values[$key] = 1;
            }
        }

        if ($values[0]) {
            return array_sum($values);
        }

        //Ace logic
        $withoutAce = array_sum($values);
        $alts = [$withoutAce+1, $withoutAce+11];
        if($alts[1]==21) return 21;
        return array_filter($alts, fn($total)=>$total<21) ?: $alts[0];
    }

    public function standValue():int{
        if(!$this->value()) dd($this);
        return max((array)$this->value());
    }

    public function isNatural(): bool
    {
        if (count($this->hand) != 2) {
            return false;
        }
        $card1 = $this->hand[0];
        $card2 = $this->hand[1];
        return (
            ($card1->rank() == Rank::ACE && Blackjack::getCardValue($card2) == 10)
            || ($card2->rank() == Rank::ACE && Blackjack::getCardValue($card1) == 10)
        );
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    public function jsonSerialize(): array
    {
        return [
            'hand'     => array_map(fn(Card|null $card) => $card?->value, $this->hand),
            'currency' => $this->wager ? $this->currency->symbol : null,
            'wager'    => $this->wager ?: null,
        ];
    }
}

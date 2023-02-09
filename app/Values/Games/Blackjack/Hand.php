<?php

namespace App\Values\Games\Blackjack;

use App\Casters\Games\Blackjack\HandCaster;
use App\Enums\Cards\Card;
use App\Enums\Cards\Rank;
use App\Models\Currency;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;

#[CastWith(HandCaster::class)]
class Hand extends DataTransferObject implements JsonSerializable, Arrayable
{
    public ?Currency $currency = null;
    /**
     * @var Card[]
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
        return $this->getCardValue($this->hand[0]) === $this->getCardValue($this->hand[1]);
    }

    protected function getCardValue(Card $card): ?int
    {
        $rank = $card->rank();
        return match ($rank->value) {
            1              => null,
            10, 11, 12, 13 => 10,
            default        => $rank->value
        };
    }

    public function isNatural(): bool
    {
        if (count($this->hand) != 2) {
            return false;
        }
        $card1 = $this->hand[0];
        $card2 = $this->hand[1];
        return (
            ($card1->rank() == Rank::ACE && $this->getCardValue($card2) == 10)
            || ($card2->rank() == Rank::ACE && $this->getCardValue($card1) == 10)
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

<?php

namespace App\Casters\Games\Blackjack;

use App\Enums\Cards\Card;
use App\Models\Currency;
use App\Values\Games\Blackjack\Hand;

class HandCaster implements \Spatie\DataTransferObject\Caster
{

    public function cast(mixed $value): Hand
    {
        $hand = new Hand;
        $hand->currency = is_string($value['currency']) ? Currency::fromSymbol($value['currency']) : $value['currency'];
        $hand->wager = $value['wager'] ?: null;
        foreach ($value['hand'] as $card) {
            $hand->hand[] = Card::from($card);
        }
        return $hand;
    }
}

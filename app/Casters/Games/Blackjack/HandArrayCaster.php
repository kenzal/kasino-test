<?php

namespace App\Casters\Games\Blackjack;

use App\Enums\Cards\Card;
use App\Models\Currency;
use App\Values\Games\Blackjack\Hand;
use Exception;

class HandArrayCaster implements \Spatie\DataTransferObject\Caster
{

    /**
     * @param  mixed  $value
     *
     * @return Hand[]
     * @throws Exception
     */
    public function cast(mixed $value): array
    {
        if (! is_array($value)) {
            throw new Exception("Can only cast arrays to Foo");
        }

        return array_map(
            function (array $data) {
                $hand = new Hand;
                $hand->currency = is_string($data['currency'])
                    ? Currency::fromSymbol($data['currency'])
                    : Currency::fromSymbol(data_get($data['currency'], 'symbol'));
                $hand->wager = $data['wager'] ?: null;
                foreach ($data['hand'] as $card) {
                    $hand->hand[] = Card::from($card);
                }
                return $hand;
            },
            $value
        );

    }
}

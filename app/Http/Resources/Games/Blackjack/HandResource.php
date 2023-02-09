<?php

namespace App\Http\Resources\Games\Blackjack;

use App\Enums\Cards\Card;
use App\Models\Currency;
use App\Values\Games\Blackjack\Hand;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @mixin Hand
 */
class HandResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        $currency = data_get($this->resource, 'currency');
        if($currency) {
            $currency = is_string($currency) ? $currency : data_get($currency, 'symbol');
            $currency = Currency::fromSymbol($currency);
        } else {
            $currency = Currency::firstOrFail();
        }
        return [
            'wager' => $this->when($currency,floatval($currency->toDisplay(data_get($this->resource, 'wager', '')))),
            'hand' => array_map(
                fn(Card|int|null $card)=>is_null($card) ? null : (is_int($card) ? Card::from($card) : $card)->name,
                data_get($this->resource, 'hand')
            ),
        ];
    }
}

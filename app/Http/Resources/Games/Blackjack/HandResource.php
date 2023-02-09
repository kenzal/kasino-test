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
        $currency = $this->currency;
        $wager = $this->wager ? $this->currency->toDisplay($this->wager) : null;
        return [
            'wager' => $this->when($wager,floatval($wager)),
            'hand' => array_map(
                fn(Card|null $card)=>is_null($card) ? null : $card->name,
                data_get($this->resource, 'hand')
            ),
            'showing' => $this->when($this->hasHiddenCards(),$this->value()),
            'value' => $this->when(!$this->hasHiddenCards(),$this->value()),
        ];
    }
}

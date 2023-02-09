<?php

namespace App\Http\Resources\Games;

use App\Http\Resources\GameResource;
use App\Http\Resources\Games\Blackjack\HandCollection;
use App\Http\Resources\Games\Blackjack\HandResource;
use App\Models\Games\Blackjack;
use App\Models\Games\Dice;
use App\Models\Round;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;

/**
 * @mixin Blackjack
 */
class BlackjackResource extends GameResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        $finalRound = $this->lastRound->refresh();

        $result      = $finalRound->result;
        $dealersHand = $result->dealer;

        $dealersHand->hand[0] = null;
        $response = array_merge(

            [
                'actions'     => $result->actions,
                'dealer'      => new HandResource($dealersHand),
                'active_hand' => $this->when(count($result->hands) > 1, $result->active_hand),
                'player' => new HandCollection($finalRound->result->hands),
            ],
            parent::toArray($request),
        );
        ksort($response);
        return $response;
    }
}

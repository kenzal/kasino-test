<?php

namespace App\Http\Resources\Games;

use App\Http\Resources\GameResource;
use App\Models\Games\Razzle;
use App\Models\Round;
use App\Values\Games\Razzle\RoundResult;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;

/**
 * @mixin Razzle
 */
class RazzleResource extends GameResource
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
        /** @var RoundResult $result */
        $result = $finalRound->result;
        return array_merge(parent::toArray($request),
                           [
                               'prize'         => $this->prize,
                               'current_wager' => $result->currentWager,
                               'result_type'   => $result->rollResultType,
                               'points'        => $result->totalPoints,
                               'result'        => $this->result,
                               'cups'          => $result->cups,
                               'round'         => $finalRound->game_round,
                               'board'         => $this->board->cups,
                               'total'         => $result->cupSum,
                               'roll_result'   => $result->rollResult,

                           ]);
    }
}

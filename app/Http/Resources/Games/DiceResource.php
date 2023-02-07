<?php

namespace App\Http\Resources\Games;

use App\Http\Resources\GameResource;
use App\Models\Games\Dice;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;

/**
 * @mixin Dice
 */
class DiceResource extends GameResource
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
        return [
            'id'                => $this->id,
            'timestamp'         => $this->created_at,
            'name'              => $this->name,
            'currency'          => $this->currency->symbol,
            'amount'            => floatval($this->currency->toDisplay($this->amount)),
            'result'            => floatval($this->currency->toDisplay($this->result)),
            'raw_amount'        => $this->amount,
            'raw_result'        => $this->result,
            'is_winner'         => $this->is_winner,
            'multiplier'        => sprintf('%0.00f', $this->multiplier),
            'client_seed'       => $this->seed->client_seed,
            'server_seed'       => $this->seed->revealed_at ? $this->seed->server_seed : null,
            'server_seed_hash'  => $this->seed->server_seed_hashed,
            'nonce'             => $this->nonce,
            'target'            => $this->target,
            'target_multiplier' => $this->target_multiplier,
            'win_chance'        => $this->win_chance,
            'direction'         => ucfirst($this->direction->value),
        ];
    }
}

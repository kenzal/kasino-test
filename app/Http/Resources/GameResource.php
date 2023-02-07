<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @mixin Game
 */
class GameResource extends JsonResource
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
            'id'               => $this->id,
            'timestamp'        => $this->created_at,
            'name'             => $this->name,
            'currency'         => $this->currency->symbol,
            'amount'           => floatval($this->currency->toDisplay($this->amount)),
            'result'           => floatval($this->currency->toDisplay($this->result)),
            'raw_amount'       => $this->amount,
            'raw_result'       => $this->result,
            'is_winner'        => $this->is_winner,
            'multiplier'       => sprintf('%0.00f', $this->multiplier),
            'client_seed'      => $this->seed->client_seed,
            'server_seed'      => $this->seed->revealed_at ? $this->seed->server_seed : null,
            'server_seed_hash' => $this->seed->server_seed_hashed,
            'nonce'            => $this->nonce,
        ];
    }
}

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
        return array_merge(parent::toArray($request), [
            'target'            => $this->target,
            'target_multiplier' => $this->target_multiplier,
            'win_chance'        => $this->win_chance,
            'direction'         => ucfirst($this->direction->value),
            'lucky_number'      => $this->lucky_number,
        ]);
    }
}

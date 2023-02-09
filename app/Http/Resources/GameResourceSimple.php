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
class GameResourceSimple extends JsonResource
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
            'id'         => $this->id,
            'href'       => route('game', $this),
            'timestamp'  => $this->created_at,
            'completed_at' => $this->completed_at,
            'name'       => $this->name,
            'currency'   => $this->currency->symbol,
            'amount'     => floatval($this->currency->toDisplay($this->amount)),
            'completed'  => $this->isCompleted,
            $this->mergeWhen(
                $this->isCompleted,
                [
                    'result'     => floatval($this->currency->toDisplay($this->result ?? '')),
                    'is_winner'  => $this->is_winner,
                    'multiplier' => floatval(sprintf('%0.02f', $this->multiplier)),
                ]
            ),
            $this->mergeWhen($this->relationLoaded('user'), [
                'username' => $this->user->name,
            ])

        ];
    }
}

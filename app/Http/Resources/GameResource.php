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
            'id'           => $this->id,
            'href'         => route('game', $this),
            'timestamp'    => $this->created_at,
            'completed_at' => $this->completed_at,
            'name'         => $this->name,
            'currency'     => $this->currency->symbol,
            'amount'       => floatval($this->currency->toDisplay($this->amount)),
            'raw_amount'   => $this->amount,
            'username'     => $this->user->name,
            'completed'    => $this->isCompleted,
            $this->mergeWhen(
                $this->isCompleted,
                [
                    'result'      => floatval($this->currency->toDisplay($this->result)),
                    'raw_result'  => $this->result,
                    'is_winner'   => $this->is_winner,
                    'multiplier'  => sprintf('%0.00f', $this->multiplier),
                    'round_count' => $this->rounds()->count(),
                ]),
            $this->mergeWhen(
                !$this->isCompleted && $this->user_id == $request->user()->id,
                [
                    'actions' => array_map(
                        fn($action) => [
                            'action' => $action,
                            'href'   => route('gameAction', ['action' => $action, 'game' => $this])
                        ],
                        $this->getActions()),
                ]
            )
        ];
    }
}

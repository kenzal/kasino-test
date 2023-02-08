<?php

namespace App\Http\Resources\Games;

use App\Enums\Games\Gems\Gems;
use App\Http\Resources\GameResource;
use App\Models\Games\Dice;
use App\Models\Round;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;

/**
 * @mixin Dice
 */
class GemsResource extends GameResource
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
        /** @var Round $finalRound */
        $finalRound = $this->rounds()->orderByDesc('game_round')->first();
        return array_merge(
            parent::toArray($request),
            [
                $this->mergeWhen(
                    $this->isCompleted,
                    [
                        'outcome' => array_map(
                            fn(int $symbol) => [
                                'value' => $symbol,
                                'color' => Gems::tryFrom($symbol)->getColor(),
                                'name'  => Gems::tryFrom($symbol)->getGem(),
                                'found' => $this->when(count($found=array_keys(array_filter($finalRound->result, fn($n)=>$n==$symbol)))>1,$found),
                            ],
                            $finalRound->result
                        )
                    ])
            ]);
    }
}

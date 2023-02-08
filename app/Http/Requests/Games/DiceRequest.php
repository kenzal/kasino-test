<?php

namespace App\Http\Requests\Games;

use App\Enums\OverUnder;
use App\Http\Requests\GameRequest;
use App\Models\Games\Dice;
use Illuminate\Validation\Rules\Enum;

/**
 * @property OverUnder|string $direction
 * @property float  $target
 * @property float  $target_multiplier
 * @property float  $win_chance
 */
class DiceRequest extends GameRequest
{

    public function gameRules(): array
    {
        return [
            'direction'         => [
                'required',
                new Enum(OverUnder::class)
            ],
            'target'            => [
                'numeric',
                ['between', 0.01, 99.99],
                'required_without_all:target_multiplier,win_chance'
            ],
            'target_multiplier' => [
                'numeric',
                ['between', Dice::multiplierMin(), Dice::multiplierMax()],
                'required_without_all:target,win_chance'
            ],
            'win_chance'        => [
                'numeric',
                ['between', 0.01, 99.99],
                'required_without_all:target,target_multiplier'
            ],
        ];
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();
        $this->merge(
            [
                'direction' => OverUnder::from(strtolower($this->direction)),
            ]
        );
    }
}

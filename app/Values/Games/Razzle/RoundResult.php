<?php

namespace App\Values\Games\Razzle;

use App\Casters\Games\Razzle\RollResultTypeCaster;
use App\Enums\Dice\D6;
use App\Enums\Games\Razzle\RollResultType;
use JessArcher\CastableDataTransferObject\CastableDataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

class RoundResult extends CastableDataTransferObject
{
    /**
     * @var int[] Cup addresses (8x 0-179)
     */
    public array $cups = [];

    /**
     * @var D6[] Cup Values (8d6)
     */
    public array $values = [];

    public int $totalPoints = 0;

    #[CastWith(RollResultTypeCaster::class)]
    public ?RollResultType $rollResultType = null;

    public ?int $rollResult = null;

    public string $currentWager = '1';

    public ?int $cupSum;

    public function calculateCupSum(): int {
        return $this->cupSum = array_sum(array_map(fn (D6 $cup) => $cup->value, $this->values));
    }


}

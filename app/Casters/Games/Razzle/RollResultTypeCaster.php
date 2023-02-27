<?php

namespace App\Casters\Games\Razzle;

use App\Enums\Games\Razzle\RollResultType;

class RollResultTypeCaster implements \Spatie\DataTransferObject\Caster
{
    public function cast(mixed $value): ?RollResultType
    {
        if(is_null($value)) return null;
        return RollResultType::from($value);
    }
}

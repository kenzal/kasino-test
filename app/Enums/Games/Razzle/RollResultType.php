<?php

namespace App\Enums\Games\Razzle;

enum RollResultType: string
{
    case DOUBLER    = 'doubler';
    case MULTIPLIER = 'multiplier';
    case NOTHING    = 'nothing';
    case POINTS     = 'points';
}

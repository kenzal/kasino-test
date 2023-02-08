<?php

namespace App\Enums\Cards;

enum Rank: int
{
    case ACE   = 1;
    case TWO   = 2;
    case THREE = 3;
    case FOUR  = 4;
    case FIVE  = 5;
    case SIX   = 6;
    case SEVEN = 7;
    case EIGHT = 8;
    case NINE  = 9;
    case TEN   = 10;
    case JACK  = 11;
    case QUEEN = 12;
    case KING  = 13;


    public function symbol(): string
    {
        return match ($this) {
            self::ACE   => 'A',
            self::TWO   => '2',
            self::THREE => '3',
            self::FOUR  => '4',
            self::FIVE  => '5',
            self::SIX   => '6',
            self::SEVEN => '7',
            self::EIGHT => '8',
            self::NINE  => '9',
            self::TEN   => '10',
            self::JACK  => 'J',
            self::QUEEN => 'Q',
            self::KING  => 'K',
        };
    }

}

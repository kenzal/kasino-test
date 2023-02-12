<?php

namespace App\Enums\Cards;

enum Suit: string
{
    case SPADES   = 'spades';
    case HEARTS   = 'hearts';
    case CLUBS    = 'clubs';
    case DIAMONDS = 'diamonds';

    public function symbol():string
    {
        return match ($this) {
            self::SPADES   => '♠',
            self::HEARTS   => '♥',
            self::CLUBS    => '♣',
            self::DIAMONDS => '♦'
        };
    }

}

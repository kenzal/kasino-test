<?php

namespace App\Enums\Cards;

enum Suit: string
{
    case SPADES   = 'Spades';
    case HEARTS   = 'Hearts';
    case CLUBS    = 'Clubs';
    case DIAMONDS = 'Diamonds';

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

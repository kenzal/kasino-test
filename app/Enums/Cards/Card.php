<?php

namespace App\Enums\Cards;

enum Card: int
{
    case ACE_OF_SPADES     = 0;
    case TWO_OF_SPADES     = 1;
    case THREE_OF_SPADES   = 2;
    case FOUR_OF_SPADES    = 3;
    case FIVE_OF_SPADES    = 4;
    case SIX_OF_SPADES     = 5;
    case SEVEN_OF_SPADES   = 6;
    case EIGHT_OF_SPADES   = 7;
    case NINE_OF_SPADES    = 8;
    case TEN_OF_SPADES     = 9;
    case JACK_OF_SPADES    = 10;
    case QUEEN_OF_SPADES   = 11;
    case KING_OF_SPADES    = 12;
    case ACE_OF_HEARTS     = 13;
    case TWO_OF_HEARTS     = 14;
    case THREE_OF_HEARTS   = 15;
    case FOUR_OF_HEARTS    = 16;
    case FIVE_OF_HEARTS    = 17;
    case SIX_OF_HEARTS     = 18;
    case SEVEN_OF_HEARTS   = 19;
    case EIGHT_OF_HEARTS   = 20;
    case NINE_OF_HEARTS    = 21;
    case TEN_OF_HEARTS     = 22;
    case JACK_OF_HEARTS    = 23;
    case QUEEN_OF_HEARTS   = 24;
    case KING_OF_HEARTS    = 25;
    case ACE_OF_CLUBS      = 26;
    case TWO_OF_CLUBS      = 27;
    case THREE_OF_CLUBS    = 28;
    case FOUR_OF_CLUBS     = 29;
    case FIVE_OF_CLUBS     = 30;
    case SIX_OF_CLUBS      = 31;
    case SEVEN_OF_CLUBS    = 32;
    case EIGHT_OF_CLUBS    = 33;
    case NINE_OF_CLUBS     = 34;
    case TEN_OF_CLUBS      = 35;
    case JACK_OF_CLUBS     = 36;
    case QUEEN_OF_CLUBS    = 37;
    case KING_OF_CLUBS     = 38;
    case ACE_OF_DIAMONDS   = 39;
    case TWO_OF_DIAMONDS   = 40;
    case THREE_OF_DIAMONDS = 41;
    case FOUR_OF_DIAMONDS  = 42;
    case FIVE_OF_DIAMONDS  = 43;
    case SIX_OF_DIAMONDS   = 44;
    case SEVEN_OF_DIAMONDS = 45;
    case EIGHT_OF_DIAMONDS = 46;
    case NINE_OF_DIAMONDS  = 47;
    case TEN_OF_DIAMONDS   = 48;
    case JACK_OF_DIAMONDS  = 49;
    case QUEEN_OF_DIAMONDS = 50;
    case KING_OF_DIAMONDS  = 51;

    public function rank(): Rank
    {
        return Rank::from((int) floor($this->value % 13) + 1);
    }

    public function suit(): Suit
    {
        return match (((int) floor($this->value / 13))) {
            0 => Suit::SPADES,
            1 => Suit::HEARTS,
            2 => Suit::CLUBS,
            3 => Suit::DIAMONDS,
        };
    }
}

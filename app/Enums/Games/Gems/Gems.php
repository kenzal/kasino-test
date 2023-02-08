<?php

namespace App\Enums\Games\Gems;

enum Gems: int
{
    case GREY   = 0;
    case RED    = 1;
    case YELLOW = 2;
    case GREEN  = 3;
    case TEAL   = 4;
    case BLUE   = 5;
    case PURPLE = 6;

     public function getColor(): string
    {
        return match ($this) {
            self::GREY   => '#b4b4b4',
            self::RED    => '#ff1c20',
            self::YELLOW => '#ffe900',
            self::GREEN  => '#11a62f',
            self::TEAL   => '#40E0D0',
            self::BLUE   => '#0e3edd',
            self::PURPLE => '#a62dc5',
        };
    }

    public function getGem(): string
    {
        return match ($this) {
            self::GREY   => 'Diamond',
            self::RED    => 'Ruby',
            self::YELLOW => 'Citrine',
            self::GREEN  => 'Emerald',
            self::TEAL   => 'Turquoise',
            self::BLUE   => 'Sapphire',
            self::PURPLE => 'Amethyst',
        };
    }
}

<?php
return [
    'blackjack' => [
        'charlie'        => (int) env('GAMES_BLACKJACK_CHARLIE', 0),
        'max_hands'      => (int) env('GAMES_BLACKJACK_MAX_HANDS', 4),
        'split_on_value' => (bool) env('GAMES_BLACKJACK_SPLIT_ON_VALUE', true),
    ]
];

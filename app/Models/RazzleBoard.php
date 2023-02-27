<?php

namespace App\Models;

use App\Enums\Dice\D6;
use InvalidArgumentException;
use Random\Engine\PcgOneseq128XslRr64 as RngEngine;
use Random\Randomizer;

/**
 * @property string $cups
 * @property string $seed
 */
class RazzleBoard extends BaseModel
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'seed';
    protected $keyType = 'string';


    protected static function getSortedArray(): array
    {
        return array_merge(
            array_fill(0, 9, D6::ONE->value),
            array_fill(0, 23, D6::TWO->value),
            array_fill(0, 58, D6::THREE->value),
            array_fill(0, 58, D6::FOUR->value),
            array_fill(0, 23, D6::FIVE->value),
            array_fill(0, 9, D6::SIX->value)
        );
    }

    /**
     * @param  string    $seed
     *
     * @return RazzleBoard
     */
    public static function fromSeed(string $seed): RazzleBoard
    {
        $seed = strtolower($seed);
        if (!preg_match(pattern: '/^[0-9a-f]{32}$/', subject: $seed)) {
            throw new InvalidArgumentException('seed must be 32 hexit string');
        }

        /**
         * @var self $board
         * @noinspection PhpDynamicAsStaticMethodCallInspection
         */
        $board = self::find($seed);

        if (!$board) {
            $board = new self;
            $board->seed = $seed;
            $rngSeed     = hex2bin($seed);
            $engine      = new RngEngine($rngSeed);
            $randomizer  = new Randomizer($engine);
            $array = $randomizer->shuffleArray(self::getSortedArray());
            $board->cups = implode('', $array);
            $board->save();
        }

        return $board;
    }

    /**
     * @param  int[]  $cups
     *
     * @return D6[]
     */
    public function getValues(array $cups): array
    {
        return array_map(fn (int $cup) => D6::from((int)substr($this->cups,$cup,1)), $cups);
    }
}

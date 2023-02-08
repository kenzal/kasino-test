<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (explode(',', env('CURRENCIES')) as $symbol) {
            $currency = Currency::firstOrCreate(
                ['symbol' => $symbol],
                [
                    'decimals'         => 18,
                    'display_decimals' => 2,
                    'name'             => $this->getCurrencyName($symbol),
                ]);

            $currency->chain    ??= env("CURRENCY_{$symbol}_CHAIN") ?: null;
            $currency->contract ??= env("CURRENCY_{$symbol}_CONTRACT") ?: null;
            $currency->save();
        }
    }

    function getCurrencyName(string $symbol): string
    {
        return match ($symbol) {
            'KFP'   => 'Kasino Fun Points',
            'KAGD'  => 'Kasino Silver',
            'KAUD'  => 'Kasino Gold',
            default => 'Unknown',
        };
    }
}

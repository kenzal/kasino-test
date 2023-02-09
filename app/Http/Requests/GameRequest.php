<?php

namespace App\Http\Requests;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $amount
 * @property string|Currency $currency
 */
abstract class GameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize():bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules():array
    {
        $allGames = [

            'currency' => ['required', Rule::exists(Currency::class, 'symbol')],
            'amount'   => ['required', 'numeric']
        ];
        return array_merge($allGames, $this->gameRules());
    }

    public abstract function gameRules(): array;

    protected function passedValidation()
    {
        $this->merge(
            [
                'currency' => Currency::fromSymbol($this->currency),
                'amount'   => Currency::fromSymbol($this->currency)
                                      ->fromDisplay($this->amount),
            ]);

    }
}

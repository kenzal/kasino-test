<?php

namespace App\Models;

use App\Http\Resources\GameResource;
use App\Models\Traits\Relations\BelongsToUser;
use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property      string   $amount
 * @property      Carbon   $completed_at
 * @property-read Carbon   $created_at
 * @property      Currency $currency
 * @property      int      $currency_id
 * @property-read int      $id
 * @property-read bool     $isCompleted
 * @property-read bool     $is_winner
 * @property-read string   $multiplier
 * @property      string   $name
 * @property      string   $result
 */
class Game extends BaseModel
{
    use BelongsToUser;

    public $timestamps = false;

    protected $fillable = [
        'amount',
        'currency_id',
        'name',
        'result',
        'user_id',
    ];

    protected $with = ['currency'];

    protected $casts = [
        'is_winner' => 'boolean',
    ];

    public function currency(): BelongsTo|Currency
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasMany|Round[]
     */
    public function rounds(): HasMany|array
    {
        return $this->hasMany(Round::class, 'game_id')->orderBy('game_round');
    }

    public function toResource(string $resource = null): JsonResource
    {
        $resource ??= class_exists($found = str_replace('App\Models', 'App\Http\Resources', static::class).'Resource')
            ? $found
            : GameResource::class;
        if (!is_subclass_of($resource, JsonResource::class)) {
            throw new \InvalidArgumentException;
        }
        return new $resource($this);
    }


    protected function isCompleted(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (bool) $this->completed_at,
        );
    }


    public function newFromBuilder($attributes = [], $connection = null)
    {
        if (data_get($attributes, 'name') && class_exists('\App\Models\Games\\'.ucfirst(data_get($attributes,
                    'name')))) {

            /** @var Game $tmp */
            $tmp = new ('\App\Models\Games\\'.ucfirst(data_get($attributes, 'name')));

            $model = $tmp->setConnection($connection)->newInstance([], true);

            $model->setRawAttributes((array) $attributes, true);

            $model->setConnection($connection ?: $this->getConnectionName());

            $model->fireModelEvent('retrieved', false);

            return $model;
        }
        return parent::newFromBuilder($attributes, $connection);
    }

    public function play(): self
    {
        throw new BadMethodCallException("Greetings Professor Falken");
    }

    public function getActions(): array
    {
        return [];
    }
}

<?php

namespace App\Models;

use App\Http\Resources\GameResource;
use App\Models\Games\Blackjack;
use App\Models\Traits\Relations\BelongsToUser;
use App\Models\Traits\Relations\HasRounds;
use App\Support\Collections\GameCollection;
use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

/**
 * @property      string             $amount
 * @property      Carbon             $completed_at
 * @property-read Carbon             $created_at
 * @property      Currency           $currency
 * @property      int                $currency_id
 * @property-read int                $id
 * @property-read bool               $isCompleted
 * @property-read bool               $is_winner
 * @property-read string             $multiplier
 * @property      string             $name
 * @property      string             $result
 * @property      Collection|Round[] $rounds
 */
class Game extends BaseModel
{
    use BelongsToUser;
    use HasRounds;

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
        'completed_at' => 'datetime',
        'created_at'   => 'datetime',
        'is_winner'    => 'boolean',
    ];

    public static function firstOpenGame(User $user): ?Blackjack
    {
        /** @var Blackjack $game */
        $game = self::query()
                     ->where('user_id', $user->id)
                     ->whereNull('completed_at')
                     ->first();
        return $game;
    }

    public function newCollection(array $models = []): GameCollection
    {
        return new GameCollection($models);
    }

    public function scopeComplete(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function currency(): BelongsTo|Currency
    {
        return $this->belongsTo(Currency::class);
    }

    public function toResource(string $resource = null): JsonResource
    {
        $resource ??= class_exists($found = str_replace('App\Models', 'App\Http\Resources', static::class).'Resource')
            ? $found
            : GameResource::class;
        if (!is_subclass_of($resource, JsonResource::class)) {
            throw new InvalidArgumentException;
        }
        return new $resource($this);
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

    public function forfeit(): bool
    {
        if (!$this->isCompleted) {
            $this->result       = bcsub($this->amount, $this->getContractedAmount(), 20);
            $this->completed_at = now();
        }
        return true;
    }

    public function getContractedAmount(): string
    {
        return $this->amount;
    }

    protected function increaseWager(string $amount): self
    {
        $this->amount = bcadd($this->amount, $amount);
        return $this;
    }

    protected function isCompleted(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (bool) $this->completed_at,
        );
    }
}

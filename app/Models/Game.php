<?php

namespace App\Models;

use App\Exceptions\Games\GameImmutableException;
use App\Http\Resources\GameResource;
use App\Models\Traits\Relations\BelongsToUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property      string   $amount
 * @property-read Carbon   $created_at
 * @property      Currency $currency
 * @property      int      $currency_id
 * @property-read int      $id
 * @property-read bool     $is_winner
 * @property-read string   $multiplier
 * @property      string   $name
 * @property      int      $nonce
 * @property      string   $result
 * @property      Seed     $seed
 * @property      int      $seed_id
 * @property      int      $user_id
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
        'seed_id',
        'user_id',
    ];

    protected $with = ['currency'];

    protected $casts = [
        'is_winner' => 'boolean',
    ];

    public function seed(): BelongsTo|Seed
    {
        return $this->belongsTo(Seed::class);
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
        if(!is_subclass_of($resource, JsonResource::class)) {
            throw new \InvalidArgumentException;
        }
        return new $resource($this);
    }

    /**
     * @throws GameImmutableException
     */
    public function refreshNonce(): self
    {
        if ($this->exists) {
            throw new GameImmutableException;
        }
        $userId      = $this->user_id ?? $this->user->id;
        $seedId      = $this->seed_id ?? $this->seed->id;
        $this->nonce = Game::query()
            ->where('user_id', $userId)
            ->where('seed_id', $seedId)
            ->selectRaw('COALESCE(MAX(nonce),0)+1 as "nonce"')->first()->nonce;
        return $this;

    }

    protected function nonce(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ?? $this->refreshNonce()->getAttributeValue('nonce'),
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
}

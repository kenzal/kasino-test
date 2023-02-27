<?php

namespace App\Models;

use App\Exceptions\Seed\RevealedException;
use App\Exceptions\SeedException;
use App\Models\Traits\Relations\BelongsToUser;
use App\Support\Collections\GameCollection;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property      string $client_seed
 * @property-read Carbon $created_at
 * @property-read int    $id
 * @property      Carbon $revealed_at
 * @property      string $server_seed
 * @property-read string $server_seed_hashed
 * @property      int    $user_id
 */
class Seed extends BaseModel
{
    use BelongsToUser;

    public    $timestamps = false;
    protected $casts      = [
        'created_at'  => 'datetime',
        'revealed_at' => 'datetime',
    ];
    protected $fillable   = [
        'client_seed',
        'created_at',
        'server_seed',
        'user_id',
    ];
    protected $hidden     = [
        'server_seed',
    ];


    /**
     * @throws RevealedException
     */
    public function cycle(string $clientSeed = null): self
    {
        if($this->isRevealed()) {
            throw new RevealedException;
        }
        /** @var GameCollection $games */
        $games = $this->games()->incomplete()->get();
        $games->forfeitAll();
        $this->revealed_at = now();
        $newSeed = self::newInstance(
            [
                'user_id'     => $this->user_id,
                'client_seed' => $clientSeed ?? self::generateRandomSeed(),
                'server_seed' => self::generateRandomSeed(),
                'created_at'  => $this->revealed_at,
            ]
        );

        $this->save();
        $newSeed->save();
        return $newSeed;
    }

    /**
     * @return HasMany|Game[]
     */
    public function games(): HasMany|array
    {
        return $this->hasMany(Game::class);
    }

    public static function generateRandomSeed(): string
    {

        try {
            $seedSeed =  random_bytes(1024);
        } catch (Exception $e) {
            $seedSeed = extension_loaded('openssl')
                ? openssl_random_pseudo_bytes(1024)
                : str_shuffle(uniqid(mt_rand(0, 1024), true));
        }
        return hash('sha256', $seedSeed);
    }

    protected function getArrayableItems(array $values)
    {
        $this->makeVisibleIf(fn() => $this->revealed_at, 'server_seed');
        return parent::getArrayableItems($values);
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read Carbon            $created_at
 * @property      Seed              $currentSeed
 * @property      string            $email
 * @property      Carbon            $email_verified_at
 * @property      Collection|Game[] $games
 * @property-read int               $id
 * @property      string            $name
 * @property      Collection|Seed[] $oldSeeds
 * @property      string            $password
 * @property      string            $remember_token
 * @property      Collection|Seed[] $seeds
 * @property-read Carbon            $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'name',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function currentSeed(): HasOne|Seed
    {
        /** @var Seed $seed */
        $seed = $this->seeds()->whereNull('revealed_at')->firstOrNew();
        while (!$seed->exists) {
            try {
                $seed->server_seed = bin2hex(random_bytes(8));
                $seed->client_seed = bin2hex(random_bytes(8));
                $seed->save();
            } catch (QueryException $e) {
                // Duplicate Entry
            } catch (Exception $e) {
                $seed->server_seed = md5(rand());
                $seed->client_seed = md5(rand());
                $seed->save();
            }
        }
        return $this->hasOne(Seed::class)
                    ->ofMany(
                        ['id' => 'max'],
                        function (Builder $query) {
                            $query->whereNull('revealed_at');
                        });
    }

    /**
     * @return HasMany|Seed[]
     */
    public function seeds(): HasMany|array
    {
        return $this->hasMany(Seed::class);
    }

    /**
     * @return HasMany|Seed[]
     */
    public function oldSeeds(): HasMany|array
    {
        return $this->hasMany(Seed::class)->whereNotNull('revealed_at');
    }

    /**
     * @return HasMany|Game[]
     */
    public function games(): HasMany|array
    {
        return $this->hasMany(Game::class);
    }
}

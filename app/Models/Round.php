<?php

namespace App\Models;

use App\Exceptions\Games\GameImmutableException;
use App\Exceptions\Games\RoundImmutableException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 * @property      Carbon                  $created_at
 * @property      Game                    $game
 * @property      int                     $game_id
 * @property      int                     $game_round
 * @property-read int                     $id
 * @property      int                     $nonce
 * @property      Round|null              $previousRound
 * @property      int                     $previous_round_id
 * @property      string|int|object|array $result
 * @property      Seed                    $seed
 * @property      int                     $seed_id
 */
class Round extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'game_round',
        'previous_round_id',
        'result',
        'seed_id',
    ];

    protected $casts = [
        'result' => 'json',
    ];

    public function seed(): BelongsTo|Seed
    {
        return $this->belongsTo(Seed::class);
    }

    public function game(): BelongsTo|Game
    {
        return $this->belongsTo(Game::class);
    }
    public function previousRound(): BelongsTo|Round
    {
        return $this->belongsTo(Round::class);
    }

    public function getHash(): string
    {
        return hash('sha256',
                    implode(':',
                            [
                                $this->seed->server_seed,
                                $this->seed->client_seed,
                                $this->game_round,
                                $this->nonce,
                            ]
                    )
        );
    }

    protected function nonce(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ?? $this->refreshNonce()->getAttributeValue('nonce'),
        );
    }

    /**
     * @throws GameImmutableException
     */
    public function refreshNonce(): self
    {
        if ($this->exists) {
            throw new RoundImmutableException;
        }
        $seedId     = $this->seed_id ?? $this->seed->id;
        $game_round = $this->game_round;

        if($this->previousRound) {
            $this->nonce = $this->previousRound->nonce;
        } else {

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->nonce = Round::query()->where('seed_id', $seedId)
                                     ->selectRaw('COALESCE(MAX(nonce),0)+1 as "nonce"')
                                     ->first()->nonce;
        }
        return $this;
    }


}

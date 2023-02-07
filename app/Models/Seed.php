<?php

namespace App\Models;

use App\Models\Traits\Relations\BelongsToUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    use HasFactory, BelongsToUser;

    protected $dates = [
        'created_at',
        'revealed_at',
    ];

    protected $fillable = [
        'client_seed',
        'server_seed',
    ];

    public $timestamps = false;

    protected $hidden = [
        'server_seed',
    ];

    protected function getArrayableItems(array $values)
    {
        $this->makeVisibleIf(fn()=>$this->revealed_at, 'server_seed');
        return parent::getArrayableItems($values);
    }
}

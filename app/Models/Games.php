<?php

namespace App\Models;

use App\Models\Traits\Relations\BelongsToUser;

class Games extends BaseModel
{
    use BelongsToUser;

    protected $fillable = [
        'nonce',
        'amount',
        'result',
    ];
}

<?php

namespace App\Models\Traits\Relations;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToUser.
 *
 * @property User $user
 * @property int  $user_id
 */
trait BelongsToUser
{
    /**
     * @return BelongsTo|User
     */
    public function user():BelongsTo|User
    {
        return $this->belongsTo(User::class);
    }
}

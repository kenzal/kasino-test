<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property      string|null $chain
 * @property      string|null $contract
 * @property-read Carbon      $created_at
 * @property      int         $decimals
 * @property      int         $display_decimals
 * @property-read int         $id
 * @property      string      $name
 * @property      string      $symbol
 * @property-read Carbon      $updated_at

 */
class Currency extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'decimals',
        'display_decimals',
        'chain',
        'contract',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

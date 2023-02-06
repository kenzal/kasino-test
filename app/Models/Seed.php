<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seed extends BaseModel
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'revealed_at',
    ];

    protected $fillable = [
        'server_seed',
        'client_seed',
    ];

    public $timestamps = false;

    protected $hidden = [
        'server_seed',
    ];
}

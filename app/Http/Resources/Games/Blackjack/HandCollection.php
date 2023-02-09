<?php

namespace App\Http\Resources\Games\Blackjack;

use App\Http\Resources\Games\JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HandCollection extends ResourceCollection
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return parent::toArray($request);
    }
}

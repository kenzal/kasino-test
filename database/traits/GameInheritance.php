<?php

namespace Database\Traits;

use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Support\Facades\Schema;

trait GameInheritance
{
    public function newGame(string $game, Connection $connection=null) {

        $connection ??= Schema::getConnection();
        $connection->unprepared("CREATE TABLE games_{$game} (name varchar(255) default '{$game}') INHERITS (games);");
        $connection->unprepared("ALTER TABLE games_{$game} ADD CONSTRAINT games_{$game}_name CHECK (name = '{$game}');");

    }
}

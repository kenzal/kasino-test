<?php

namespace Database\Traits;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

trait GameInheritance
{
    public function newGameTable(string $game, Connection $connection=null): void {

        $connection ??= Schema::getConnection();
        $connection->unprepared("CREATE TABLE games_{$game} (name varchar(255) default '{$game}') INHERITS (games);");
        $connection->unprepared("ALTER TABLE games_{$game} ADD CONSTRAINT games_{$game}_name CHECK (name = '{$game}');");
        Schema::table("games_{$game}", function (Blueprint $table) {
            $table->primary('id');
        });
    }
}

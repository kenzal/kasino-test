<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = /** @lang PostgreSQL */ <<<SQL
            DROP TYPE IF EXISTS over_under;
            CREATE TYPE over_under AS ENUM ('over', 'under');
        SQL;
        Schema::getConnection()->unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::getConnection()->unprepared("DROP TYPE over_under IF EXISTS RESTRICT");
    }
};

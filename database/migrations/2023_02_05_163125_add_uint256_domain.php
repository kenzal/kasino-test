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
            DROP DOMAIN IF EXISTS uint256;
            CREATE DOMAIN  uint256 AS NUMERIC
            CHECK (VALUE >= 0 AND VALUE < 2^256)
            CHECK (SCALE(VALUE) = 0);
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
        Schema::getConnection()->unprepared("DROP DOMAIN IF EXISTS uint256 RESTRICT");
    }
};

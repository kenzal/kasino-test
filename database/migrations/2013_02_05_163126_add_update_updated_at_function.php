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
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
               NEW.updated_at = (now() at time zone 'utc');
               RETURN NEW;
            END;
            $$ language 'plpgsql';
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
        Schema::getConnection()->unprepared("DROP FUNCTION IF EXISTS update_updated_at_column");
    }
};

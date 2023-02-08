<?php

use App\Models\Currency;
use App\Models\Game;
use App\Models\Round;
use App\Models\seed;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('games',
            function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(User::class);
                $table->foreignIdFor(Currency::class);
                $table->addColumn('uint256', 'amount');
                $table->string('name');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('completed_at')->nullable();
                $table->addColumn('uint256', 'result')->nullable();
                $table->boolean('is_winner')->nullable()->storedAs('result > amount');
                $table->float('multiplier', 10, 4)->nullable()->storedAs('result/amount');
            });

        Schema::create('rounds',
            function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Game::class);
                $table->foreignIdFor(Seed::class);
                $table->foreignIdFor(Round::class, 'previous_round_id')->nullable();
                $table->bigInteger('nonce');
                $table->bigInteger('game_round');
                $table->json('result');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['seed_id', 'nonce', 'game_round']);
            });

        $sql = /** @lang PostgreSQL */
            <<<SQL
                DROP FUNCTION IF EXISTS nonce_available;
                CREATE OR REPLACE FUNCTION nonce_available(_nonce bigint, _seed_id bigint)
                  RETURNS bool AS
                $$
                SELECT NOT EXISTS (SELECT 1 FROM rounds WHERE nonce = $1 and seed_id = $2);
                $$  LANGUAGE sql STABLE;
            SQL;
        Schema::getConnection()->unprepared($sql);


        $sql = /** @lang PostgreSQL */
            <<<SQL
                ALTER TABLE rounds ADD CONSTRAINT round_nonce_unique
                CHECK (nonce_available(nonce, seed_id)) NOT VALID;
            SQL;

        Schema::getConnection()->unprepared($sql);
        $sql = /** @lang PostgreSQL */
            <<<SQL
                ALTER TABLE rounds ADD CONSTRAINT round_appropriate_previous
                CHECK (
                    CASE WHEN game_round = 0
                         THEN previous_round_id IS NULL
                         ELSE previous_round_id IS NOT NULL
                    END) NOT VALID;
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
        Schema::dropIfExists('rounds');
        Schema::getConnection()->unprepared('DROP FUNCTION IF EXISTS nonce_available;');
        Schema::dropIfExists('games');
    }
};

<?php

use App\Models\Currency;
use App\Models\seed;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Seed::class);
            $table->foreignIdFor(Currency::class);
            $table->bigInteger('nonce');
            $table->string('name');
            $table->addColumn('uint256', 'amount');
            $table->addColumn('uint256', 'result');
            $table->boolean('is_winner')->storedAs('result > amount');
            $table->float('multiplier', 10,4)->storedAs('result/amount');
            $table->unique(['user_id','seed_id','nonce']);
        });

        $sql = /** @lang PostgreSQL */ <<<SQL
            CREATE OR REPLACE FUNCTION nonce_available(_nonce bigint, _user_id bigint, _seed_id bigint, _id bigint)
              RETURNS bool AS
            $$
            SELECT NOT EXISTS (SELECT 1 FROM games WHERE nonce = $1 and user_id = $2 and seed_id = $3 and id<>$4);
            $$  LANGUAGE sql STABLE;
        SQL;
        Schema::getConnection()->unprepared($sql);


        $sql = /** @lang PostgreSQL */ <<<SQL
            ALTER TABLE games ADD CONSTRAINT game_nonce_unique
            CHECK (nonce_available(nonce, user_id, seed_id, id)) NOT VALID;
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
        Schema::dropIfExists('games');
    }
};

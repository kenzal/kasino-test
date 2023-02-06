<?php

use Database\Traits\GameInheritance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use GameInheritance;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->newGame('dice');
        Schema::table('games_dice', function (Blueprint $table) {
            $table->primary('id');
            $table->unique(['seed_id','nonce']);
            $table->decimal('target', 8,2);
            $table->decimal('target_multiplier',10,4);
            $table->decimal('win_chance', 8,6);
            $table->addColumn('overUnder', 'direction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games_dices');
    }
};

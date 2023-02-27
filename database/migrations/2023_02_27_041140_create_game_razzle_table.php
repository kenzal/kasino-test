<?php

use Database\Traits\GameInheritance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    use GameInheritance;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->newGameTable('razzle');
        Schema::table('games_razzle',
            function (Blueprint $table) {
                $table->char('razzle_board_seed', 32)->nullable();
                $table->addColumn('uint256', 'prize');
                $table->foreign('razzle_board_seed')
                      ->references('seed')
                      ->on('razzle_boards')
                      ->restrictOnDelete()
                      ->restrictOnUpdate();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('games_razzle');
    }
};

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
        $this->newGameTable('gems');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games_gems');
    }
};

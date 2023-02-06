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
            $table->unique(['seed_id','nonce']);
        });
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

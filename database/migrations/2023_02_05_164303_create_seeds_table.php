<?php

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
        Schema::create('seeds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('server_seed');
            $table->string('client_seed');
            $table->string('server_seed_hashed',64)
                ->storedAs("encode(sha256(server_seed::bytea),'hex')")
                ->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('revealed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seeds');
    }
};

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('tokens', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('spotify_id');
    //         $table->text('token')->nullable();
    //         $table->text('refresh_token')->nullable();
    //         $table->dateTime('token_at')->nullable();
    //         $table->dateTime('refresh_token_at')->nullable();
    //         $table->timestamps();
    //     });
    //     // DB::table('tokens')->insert(['user_id' => '1', ]);
    // }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::dropIfExists('tokens');
    // }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->integer('fixture_id');
            $table->foreignId('league_id')->constrained();
            $table->integer('round');
            $table->dateTime('date');
            $table->foreignId('team_home_id')->constrained('teams');
            $table->foreignId('team_away_id')->constrained('teams');
            $table->integer('score_home')->nullable();
            $table->integer('score_away')->nullable();
            $table->string('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};

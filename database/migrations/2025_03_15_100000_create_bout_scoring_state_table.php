<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bout_scoring_state', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tournament_id');
            $table->unsignedInteger('bout_id');
            $table->unsignedInteger('red_wrestler_id');
            $table->unsignedInteger('green_wrestler_id');
            $table->unsignedSmallInteger('red_score')->default(0);
            $table->unsignedSmallInteger('green_score')->default(0);
            $table->unsignedTinyInteger('period')->default(1);
            $table->unsignedInteger('clock_seconds')->default(0);
            $table->string('status', 20)->default('pending'); // pending, live, paused, completed
            $table->unsignedInteger('winner_id')->nullable();
            $table->string('result_type', 30)->nullable(); // Points, Fall, Forfeit, etc.
            // Side timers: remaining seconds per wrestler
            $table->unsignedInteger('blood_time_red')->default(300);
            $table->unsignedInteger('blood_time_green')->default(300);
            $table->unsignedInteger('injury_time_red')->default(90);
            $table->unsignedInteger('injury_time_green')->default(90);
            $table->unsignedInteger('head_neck_time_red')->default(300);
            $table->unsignedInteger('head_neck_time_green')->default(300);
            $table->unsignedInteger('recovery_time_red')->default(120);
            $table->unsignedInteger('recovery_time_green')->default(120);
            $table->timestamps();

            $table->unique(['tournament_id', 'bout_id']);
            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->foreign('red_wrestler_id')->references('id')->on('tournamentwrestlers')->onDelete('cascade');
            $table->foreign('green_wrestler_id')->references('id')->on('tournamentwrestlers')->onDelete('cascade');
            $table->foreign('winner_id')->references('id')->on('tournamentwrestlers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bout_scoring_state');
    }
};

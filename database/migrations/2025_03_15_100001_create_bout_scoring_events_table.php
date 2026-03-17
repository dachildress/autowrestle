<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bout_scoring_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tournament_id');
            $table->unsignedInteger('bout_id');
            $table->string('side', 10); // red, green, neutral
            $table->string('event_type', 50); // takedown, escape, reversal, nearfall, etc.
            $table->smallInteger('points')->default(0);
            $table->unsignedTinyInteger('period')->nullable();
            $table->unsignedInteger('match_time_snapshot')->nullable(); // seconds into match when event occurred
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['tournament_id', 'bout_id']);
            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bout_scoring_events');
    }
};

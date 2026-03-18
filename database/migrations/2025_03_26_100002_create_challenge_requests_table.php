<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tournament_id');
            $table->unsignedInteger('challenger_tournament_wrestler_id')->comment('TournamentWrestler id of wrestler issuing challenge');
            $table->unsignedInteger('challenged_tournament_wrestler_id')->comment('TournamentWrestler id of wrestler being challenged');
            $table->unsignedBigInteger('challenger_user_id')->comment('Parent account that sent the challenge');
            $table->unsignedBigInteger('challenged_user_id')->comment('Parent account that receives the challenge');
            $table->string('status', 40)->default('pending_acceptance'); // pending_acceptance, accepted_pending_director, declined_by_parent, approved_by_director, declined_by_director, scheduled, cancelled
            $table->text('director_notes')->nullable();
            $table->unsignedInteger('mat_number')->nullable();
            $table->unsignedInteger('bout_id')->nullable()->comment('Set when match is created');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('director_acted_at')->nullable();
            $table->timestamps();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->foreign('challenger_tournament_wrestler_id')->references('id')->on('tournamentwrestlers')->onDelete('cascade');
            $table->foreign('challenged_tournament_wrestler_id')->references('id')->on('tournamentwrestlers')->onDelete('cascade');
            $table->foreign('challenger_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('challenged_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tournament_id', 'status']);
            $table->index('challenged_user_id');
            $table->index('challenger_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_requests');
    }
};

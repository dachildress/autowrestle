<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bout_number_schemes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tournament_id');
            $table->string('scheme_name');
            $table->unsignedSmallInteger('start_at')->default(1);
            $table->boolean('skip_byes')->default(true);
            $table->string('match_ids', 500)->nullable();
            $table->boolean('all_mats')->default(true);
            $table->boolean('all_groups')->default(true);
            $table->boolean('all_rounds')->default(true);
            $table->json('mat_numbers')->nullable();
            $table->json('round_numbers')->nullable();
            $table->timestamps();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->index('tournament_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bout_number_schemes');
    }
};

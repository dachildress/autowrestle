<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bout_number_scheme_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bout_number_scheme_id');
            $table->unsignedInteger('tournament_id');
            $table->unsignedInteger('division_id');
            $table->unsignedInteger('group_id');
            $table->timestamps();

            $table->foreign('bout_number_scheme_id')->references('id')->on('bout_number_schemes')->onDelete('cascade');
            $table->unique(['bout_number_scheme_id', 'tournament_id', 'division_id', 'group_id'], 'scheme_tournament_division_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bout_number_scheme_groups');
    }
};

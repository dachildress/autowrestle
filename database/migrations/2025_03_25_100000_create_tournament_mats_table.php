<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_mats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tournament_id');
            $table->unsignedSmallInteger('mat_number')->comment('1-based mat number');
            $table->string('name', 100)->nullable()->comment('Display name e.g. Mat 1');
            $table->string('constraint', 100)->nullable()->comment('e.g. elementary, small mats');
            $table->timestamps();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->unique(['tournament_id', 'mat_number']);
            $table->index('tournament_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_mats');
    }
};

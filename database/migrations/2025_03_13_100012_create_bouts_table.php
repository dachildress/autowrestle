<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bouts', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->unsignedInteger('Wrestler_Id');
            $table->unsignedInteger('Bracket_Id');
            $table->unsignedInteger('mat_number');
            $table->unsignedInteger('round')->nullable();
            $table->float('points')->default(0);
            $table->string('wrtime', 5)->nullable();
            $table->unsignedTinyInteger('pin')->default(0);
            $table->unsignedTinyInteger('color')->default(0);
            $table->unsignedTinyInteger('scored')->default(0);
            $table->unsignedInteger('Tournament_Id')->default(1)->nullable();
            $table->float('score')->nullable();
            $table->boolean('printed')->default(false);
            $table->unsignedInteger('Division_Id');
            $table->timestamps();

            $table->primary(['id', 'Wrestler_Id']);
            $table->index('Tournament_Id');
            $table->index('Bracket_Id');
            $table->index('Wrestler_Id');
            $table->foreign('Tournament_Id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->foreign('Wrestler_Id')->references('id')->on('tournamentwrestlers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bouts');
    }
};

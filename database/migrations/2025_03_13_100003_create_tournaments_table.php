<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('TournamentName', 100);
            $table->date('TournamentDate');
            $table->string('link', 255)->nullable();
            $table->text('message')->nullable();
            $table->string('AllowDouble', 1)->default('1');
            $table->unsignedInteger('status')->default(0);
            $table->date('OpenDate');
            $table->unsignedTinyInteger('ViewWrestlers')->default(0);
            $table->unsignedInteger('Type')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};

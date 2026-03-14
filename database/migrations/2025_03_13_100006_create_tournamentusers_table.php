<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournamentusers', function (Blueprint $table) {
            $table->unsignedInteger('Id', true);
            $table->unsignedInteger('Tournament_id');
            $table->unsignedInteger('User_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournamentusers');
    }
};

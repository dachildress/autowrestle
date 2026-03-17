<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unbouted', function (Blueprint $table) {
            $table->unsignedInteger('MatNumber');
            $table->unsignedInteger('Bracket_Id');
            $table->unsignedInteger('Tournament_id')->default(1)->nullable();
        });

        Schema::table('unbouted', function (Blueprint $table) {
            $table->index('Tournament_id');
            $table->index('Bracket_Id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unbouted');
    }
};

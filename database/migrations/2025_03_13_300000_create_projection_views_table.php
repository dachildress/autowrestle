<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projection_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tournament_id');
            $table->string('name', 100);
            $table->unsignedTinyInteger('wrestlers_per_mat')->default(4);
            $table->timestamps();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projection_view_groups');
        Schema::dropIfExists('projection_views');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_checklist', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tournament_id');
            $table->string('step_key', 64);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->unique(['tournament_id', 'step_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_checklist');
    }
};

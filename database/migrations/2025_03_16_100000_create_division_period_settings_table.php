<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_period_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('division_id');
            $table->string('period_code', 10);
            $table->string('period_label', 30)->nullable();
            $table->unsignedTinyInteger('sort_order');
            $table->unsignedInteger('duration_seconds');
            $table->timestamps();

            $table->unique(['division_id', 'period_code']);
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_period_settings');
    }
};

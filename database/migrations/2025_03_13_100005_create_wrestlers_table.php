<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wrestlers', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('wr_first_name', 30);
            $table->string('wr_last_name', 30);
            $table->string('wr_club', 30);
            $table->unsignedInteger('wr_age');
            $table->string('wr_grade', 10);
            $table->unsignedInteger('wr_weight')->nullable();
            $table->unsignedInteger('wr_pr');
            $table->date('wr_dob')->nullable();
            $table->unsignedInteger('wr_wins')->default(0);
            $table->unsignedInteger('wr_losses')->default(0);
            $table->unsignedInteger('wr_years');
            $table->unsignedInteger('usawnumber')->nullable();
            $table->string('coach_name', 50)->default('');
            $table->string('coach_phone', 14)->default('');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wrestlers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournamentwrestlers', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('wr_first_name', 30);
            $table->string('wr_last_name', 30);
            $table->string('wr_club', 30);
            $table->unsignedInteger('wr_age');
            $table->string('wr_grade', 10);
            $table->unsignedInteger('wr_weight')->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('wr_bracket_id')->nullable();
            $table->unsignedInteger('wr_bracket_position')->nullable();
            $table->unsignedInteger('wr_pr')->default(0);
            $table->date('wr_dob')->nullable();
            $table->unsignedInteger('wr_wins')->default(0);
            $table->unsignedInteger('wr_losses')->default(0);
            $table->unsignedInteger('wr_years');
            $table->unsignedInteger('bracketed')->default(0);
            $table->unsignedInteger('Tournament_id')->default(1);
            $table->unsignedInteger('Wrestler_Id');
            $table->boolean('checked_in')->default(false);
            $table->timestamps();

            $table->index('Tournament_id');
            $table->index('Wrestler_Id');
            $table->index('group_id');
            $table->foreign('Tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournamentwrestlers');
    }
};

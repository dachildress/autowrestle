<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divgroups', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->string('Name', 25);
            $table->unsignedInteger('MinAge');
            $table->unsignedInteger('MaxAge');
            $table->unsignedTinyInteger('MinGrade');
            $table->unsignedTinyInteger('MaxGrade');
            $table->unsignedTinyInteger('MaxWeightDiff');
            $table->string('BracketType', 20);
            $table->unsignedTinyInteger('MaxPwrDiff')->nullable();
            $table->unsignedTinyInteger('bracketed')->default(0);
            $table->unsignedInteger('bouted')->nullable()->default(0);
            $table->unsignedInteger('MaxExpDiff')->default(0);
            $table->unsignedInteger('Tournament_Id')->default(1);
            $table->unsignedInteger('Division_id');
            $table->timestamps();

            $table->primary(['id', 'Tournament_Id', 'Division_id']);
            $table->index('Tournament_Id');
            $table->index('Division_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divgroups');
    }
};

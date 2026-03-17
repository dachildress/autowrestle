<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brackets', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->unsignedInteger('wr_Id');
            $table->unsignedInteger('wr_pos');
            $table->unsignedTinyInteger('bouted')->default(0);
            $table->unsignedInteger('Tournament_Id')->default(1)->nullable();
            $table->boolean('printed')->default(false);
            $table->unsignedInteger('Division_Id');
            $table->timestamps();

            $table->primary(['id', 'wr_Id', 'wr_pos']);
            $table->index('Tournament_Id');
            $table->index('wr_Id');
            $table->foreign('Tournament_Id')->references('id')->on('tournaments')->onDelete('cascade');
            $table->foreign('wr_Id')->references('id')->on('tournamentwrestlers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brackets');
    }
};

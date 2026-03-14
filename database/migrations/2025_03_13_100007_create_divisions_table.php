<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('DivisionName', 45);
            $table->unsignedInteger('StartingMat');
            $table->unsignedInteger('TotalMats');
            $table->unsignedInteger('PerBracket');
            $table->unsignedInteger('Tournament_Id')->default(0);
            $table->unsignedInteger('bouted')->default(0);
            $table->unsignedInteger('Bracketed')->default(0);
            $table->unsignedInteger('printedbrackets')->default(0);
            $table->unsignedInteger('printedbouts')->default(0);
            $table->timestamps();

            $table->index('Tournament_Id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};

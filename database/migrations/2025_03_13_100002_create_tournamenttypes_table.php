<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournamenttypes', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('Name', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournamenttypes');
    }
};

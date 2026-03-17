<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('Club', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boutsettings', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedTinyInteger('BoutType');
            $table->unsignedTinyInteger('Round');
            $table->unsignedTinyInteger('PosNumber');
            $table->unsignedTinyInteger('AddTo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boutsettings');
    }
};

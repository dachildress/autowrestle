<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teamrequest', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('Name', 255);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teamrequest');
    }
};

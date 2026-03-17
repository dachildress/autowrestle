<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_content', function (Blueprint $table) {
            $table->string('key', 120)->primary();
            $table->string('type', 20)->default('text'); // 'text' | 'image'
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_content');
    }
};

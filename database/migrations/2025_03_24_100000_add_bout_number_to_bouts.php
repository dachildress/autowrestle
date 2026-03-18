<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bouts', function (Blueprint $table) {
            $table->unsignedInteger('bout_number')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('bouts', function (Blueprint $table) {
            $table->dropColumn('bout_number');
        });
    }
};

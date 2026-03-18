<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bout_number_schemes', function (Blueprint $table) {
            $table->boolean('same_mat_per_bracket')->default(false)->after('round_numbers');
        });
    }

    public function down(): void
    {
        Schema::table('bout_number_schemes', function (Blueprint $table) {
            $table->dropColumn('same_mat_per_bracket');
        });
    }
};

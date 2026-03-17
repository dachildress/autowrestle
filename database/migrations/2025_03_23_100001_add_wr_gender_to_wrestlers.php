<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wrestlers', function (Blueprint $table) {
            $table->string('wr_gender', 10)->nullable()->after('wr_last_name');
        });
    }

    public function down(): void
    {
        Schema::table('wrestlers', function (Blueprint $table) {
            $table->dropColumn('wr_gender');
        });
    }
};

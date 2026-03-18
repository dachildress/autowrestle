<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bouts', function (Blueprint $table) {
            $table->unsignedBigInteger('challenge_request_id')->nullable()->after('Division_Id');
        });
    }

    public function down(): void
    {
        Schema::table('bouts', function (Blueprint $table) {
            $table->dropColumn('challenge_request_id');
        });
    }
};

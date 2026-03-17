<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->date('end_date')->nullable();
            $table->string('location_name', 255)->nullable();
            $table->string('location_address', 500)->nullable();
            $table->string('contact_name', 100)->nullable();
            $table->string('contact_email', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'location_name',
                'location_address',
                'end_date',
                'contact_name',
                'contact_email',
            ]);
        });
    }
};

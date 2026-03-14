<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name', 30)->default('')->after('name');
            $table->string('phone_number', 14)->default('')->after('last_name');
            $table->char('accesslevel', 2)->default('10')->after('password');
            $table->char('active', 1)->default('0')->after('accesslevel');
            $table->string('username', 40)->default('')->after('active');
            $table->string('mycode', 10)->default('')->after('username');
            $table->unsignedInteger('Tournament_id')->default(1)->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name', 'phone_number', 'accesslevel', 'active',
                'username', 'mycode', 'Tournament_id'
            ]);
        });
    }
};

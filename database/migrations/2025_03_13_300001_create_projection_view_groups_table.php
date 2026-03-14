<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projection_view_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projection_view_id')->constrained('projection_views')->onDelete('cascade');
            $table->unsignedInteger('group_id');
            $table->unsignedInteger('division_id');
            $table->timestamps();

            $table->unique(['projection_view_id', 'group_id', 'division_id'], 'proj_view_grp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projection_view_groups');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE divgroups MODIFY MinGrade TINYINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE divgroups MODIFY MaxGrade TINYINT NOT NULL DEFAULT 0');
        }
        // SQLite and others: tinyint is stored as integer; -1 is valid. No change needed for SQLite.
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE divgroups MODIFY MinGrade TINYINT UNSIGNED NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE divgroups MODIFY MaxGrade TINYINT UNSIGNED NOT NULL DEFAULT 0');
        }
    }
};

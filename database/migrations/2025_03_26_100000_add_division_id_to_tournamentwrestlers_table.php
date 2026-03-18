<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournamentwrestlers', function (Blueprint $table) {
            $table->unsignedInteger('division_id')->nullable()->after('group_id');
            $table->index('division_id');
        });

        // Backfill: prefer division from bracket (Division_Id) when wrestler has wr_bracket_id;
        // otherwise use divgroup Division_id when exactly one group matches (id + tournament).
        // When multiple divisions share the same group id, leave null so view groups filter works after re-save.
        $pairs = DB::table('tournamentwrestlers')
            ->whereNotNull('group_id')
            ->select('id', 'group_id', 'Tournament_id', 'wr_bracket_id')
            ->get();
        foreach ($pairs as $row) {
            $divisionId = null;
            if (! empty($row->wr_bracket_id)) {
                $divisionId = DB::table('brackets')
                    ->where('id', $row->wr_bracket_id)
                    ->where('Tournament_Id', $row->Tournament_id)
                    ->value('Division_Id');
            }
            if ($divisionId === null) {
                $groups = DB::table('divgroups')
                    ->where('id', $row->group_id)
                    ->where('Tournament_Id', $row->Tournament_id)
                    ->pluck('Division_id');
                $divisionId = $groups->count() === 1 ? $groups->first() : null;
            }
            if ($divisionId !== null) {
                DB::table('tournamentwrestlers')->where('id', $row->id)->update(['division_id' => $divisionId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tournamentwrestlers', function (Blueprint $table) {
            $table->dropIndex(['division_id']);
            $table->dropColumn('division_id');
        });
    }
};

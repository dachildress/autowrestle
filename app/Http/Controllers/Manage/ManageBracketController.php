<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\Wrestler;
use App\Services\BracketGenerationService;
use App\Services\BracketPrintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManageBracketController extends Controller
{
    /**
     * Seeded boutsettings only define pairings for bracket sizes 2–6 ({@see BoutSettingsSeeder}).
     * Drag-and-drop and {@see moveWrestler} enforce this so operators are not surprised when bouting creates no bouts.
     */
    public const MAX_WRESTLERS_PER_BRACKET_FOR_BOUTING = 6;

    private function authorizeTournament(Request $request, int $tid): Tournament
    {
        $tournament = Tournament::findOrFail($tid);
        $user = $request->user();
        if ($user->isAdmin()) {
            return $tournament;
        }
        if ($tournament->users()->where('User_id', $user->id)->exists()) {
            return $tournament;
        }
        abort(403, 'You cannot manage this tournament.');
    }

    /**
     * Create brackets for a division (all groups) and redirect to first group's brackets.
     */
    public function create(Request $request, int $tid, int $did): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();

        try {
            $service = app(BracketGenerationService::class);
            $bracketRowsCreated = $service->createBracketsForDivision($tid, $did);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Bracket creation failed: ' . $e->getMessage());
        }

        $firstGroup = DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->first();
        $gid = $firstGroup ? $firstGroup->id : 0;
        $url = $gid
            ? route('manage.brackets.show', [$tid, $did, $gid])
            : route('manage.tournaments.show', $tid);

        if ($bracketRowsCreated === 0) {
            return redirect($url)->with('error', 'No wrestlers to bracket in ' . $division->DivisionName . '. Add wrestlers to groups (View Groups / registration) and ensure they have a group assigned.');
        }

        return redirect($url)->with('success', 'Brackets created for ' . $division->DivisionName . '.');
    }

    /**
     * Show bracketed wrestlers for a group (by bracket id and position).
     */
    public function show(Request $request, int $tid, int $did, int $gid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->firstOrFail();

        $wrestlers = TournamentWrestler::query()
            ->select('tournamentwrestlers.*', 'brackets.id as bracket_id', 'brackets.wr_pos as wr_pos')
            ->join('brackets', function ($j) use ($tid) {
                $j->on('tournamentwrestlers.id', '=', 'brackets.wr_Id')
                    ->where('brackets.Tournament_Id', '=', $tid);
            })
            ->where('tournamentwrestlers.group_id', $gid)
            ->where('tournamentwrestlers.division_id', $group->Division_id)
            ->where('tournamentwrestlers.Tournament_id', $tid)
            ->where('tournamentwrestlers.bracketed', 1)
            ->orderBy('brackets.id')
            ->orderBy('brackets.wr_pos')
            ->get();

        $bracketCounts = $wrestlers->groupBy('bracket_id')->map->count();
        $perBracket = $this->perBracketForGroup($group->BracketType ?? '', $division->PerBracket);

        $groupsInDivision = DivGroup::where('Division_id', $group->Division_id)
            ->where('Tournament_Id', $tid)
            ->get();

        return view('manage.brackets.show', [
            'tournament' => $tournament,
            'division' => $division,
            'group' => $group,
            'groupsInDivision' => $groupsInDivision,
            'wrestlers' => $wrestlers,
            'bracketCounts' => $bracketCounts,
            'perBracket' => $perBracket,
            'bouted' => (bool) $group->bouted,
            'maxWrestlersPerBracketForBouting' => self::MAX_WRESTLERS_PER_BRACKET_FOR_BOUTING,
        ]);
    }

    /**
     * Printable round-robin bracket sheets for a division (all brackets in bracket id order).
     */
    public function printBrackets(Request $request, int $tid, int $did): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        if (! (bool) $division->Bracketed) {
            abort(404, 'This division is not bracketed yet.');
        }

        $sheets = app(BracketPrintService::class)->buildDivisionSheets(
            $tid,
            $did,
            (bool) $division->bouted
        );

        return view('manage.brackets.print', [
            'tournament' => $tournament,
            'division' => $division,
            'sheets' => $sheets,
        ]);
    }

    /**
     * Unbracket a division: delete bouts, brackets, reset division/groups/wrestlers.
     */
    public function unbracket(Request $request, int $tid, int $did): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();

        Bout::where('Division_Id', $did)->where('Tournament_Id', $tid)->delete();
        Bracket::where('Division_Id', $did)->where('Tournament_Id', $tid)->delete();
        Division::where('id', $did)->where('Tournament_Id', $tid)->update([
            'Bracketed' => 0,
            'bouted' => 0,
            'printedbrackets' => 0,
            'printedbouts' => 0,
        ]);
        DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->update([
            'bracketed' => 0,
            'bouted' => 0,
        ]);

        $groupIds = DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->pluck('id');
        TournamentWrestler::where('Tournament_id', $tid)
            ->where('bracketed', 1)
            ->whereIn('group_id', $groupIds)
            ->where('division_id', $did)
            ->update([
                'bracketed' => 0,
                'wr_bracket_id' => null,
                'wr_bracket_position' => null,
            ]);

        return redirect()->route('manage.tournaments.show', $tid)
            ->with('success', 'Brackets cleared for ' . $division->DivisionName . '.');
    }

    /**
     * Remove a wrestler from brackets and delete their tournamentwrestler row.
     * Renumbers positions in the bracket they left.
     */
    public function deleteWrestler(Request $request, int $tid, int $wid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $row = Bracket::where('wr_Id', $wid)->where('Tournament_Id', $tid)->first();
        $bracketId = $row ? (int) $row->id : null;
        Bracket::where('wr_Id', $wid)->where('Tournament_Id', $tid)->delete();
        TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->delete();
        if ($bracketId !== null) {
            $this->renumberBracketPositions($tid, $bracketId);
        }
        return redirect()->back()->with('success', 'Wrestler removed.');
    }

    /**
     * Move a wrestler to another bracket in the same division. Renumbers positions in both brackets.
     */
    public function moveWrestler(Request $request, int $tid, int $wid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $targetBracketId = (int) $request->input('target_bracket_id');
        if ($targetBracketId < 1) {
            return redirect()->back()->with('error', 'Invalid target bracket.');
        }

        $tw = TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();
        $currentBracketId = (int) $tw->wr_bracket_id;
        if ($currentBracketId === $targetBracketId) {
            return redirect()->back()->with('info', 'Wrestler is already in that bracket.');
        }

        $divisionId = (int) Bracket::where('id', $currentBracketId)->where('Tournament_Id', $tid)->value('Division_Id');
        $valid = Bracket::where('id', $targetBracketId)->where('Tournament_Id', $tid)->where('Division_Id', $divisionId)->exists();
        if (! $valid) {
            return redirect()->back()->with('error', 'Target bracket is not in this division.');
        }

        $wrestlersInTarget = (int) Bracket::where('id', $targetBracketId)->where('Tournament_Id', $tid)->count();
        if ($wrestlersInTarget >= self::MAX_WRESTLERS_PER_BRACKET_FOR_BOUTING) {
            $message = 'That bracket already has the maximum supported for automatic bouting ('
                . self::MAX_WRESTLERS_PER_BRACKET_FOR_BOUTING
                . ' wrestlers). Remove a wrestler before adding another.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        Bracket::where('id', $currentBracketId)->where('wr_Id', $wid)->where('Tournament_Id', $tid)->delete();
        $this->renumberBracketPositions($tid, $currentBracketId);

        $nextPos = (int) Bracket::where('id', $targetBracketId)->where('Tournament_Id', $tid)->max('wr_pos') + 1;
        Bracket::insert([
            'id' => $targetBracketId,
            'wr_Id' => $wid,
            'wr_pos' => $nextPos,
            'bouted' => 0,
            'Tournament_Id' => $tid,
            'Division_Id' => $divisionId,
            'printed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->update([
            'wr_bracket_id' => $targetBracketId,
            'wr_bracket_position' => $nextPos,
        ]);
        $this->renumberBracketPositions($tid, $targetBracketId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Wrestler moved to bracket ' . $targetBracketId . '.']);
        }
        return redirect()->back()->with('success', 'Wrestler moved to bracket ' . $targetBracketId . '.');
    }

    /**
     * Show Move Wrestler form: wrestler info and group dropdown (same division).
     */
    public function moveWrestlerForm(Request $request, int $tid, int $wid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tw = TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();
        $currentGroup = DivGroup::where('id', $tw->group_id)->where('Tournament_Id', $tid)->first();
        if (! $currentGroup) {
            return redirect()->back()->with('error', 'Wrestler has no valid group.');
        }
        $division = Division::where('id', $currentGroup->Division_id)->where('Tournament_Id', $tid)->firstOrFail();
        $divisionNames = Division::where('Tournament_Id', $tid)->pluck('DivisionName', 'id');
        $wrestler = Wrestler::find($tw->Wrestler_Id);
        $wrestlerIsGirl = $wrestler && $wrestler->wr_gender === 'Girl';
        $groups = DivGroup::where('Tournament_Id', $tid)
            ->when(! $wrestlerIsGirl, fn ($q) => $q->whereRaw('LOWER(TRIM(COALESCE(`gender`, ""))) != ?', ['girls']))
            ->where(function ($q) use ($currentGroup) {
                $q->where('id', '!=', $currentGroup->id)
                    ->orWhere('Division_id', '!=', $currentGroup->Division_id);
            })
            ->orderBy('Division_id')
            ->orderBy('Name')
            ->get()
            ->map(function ($g) use ($divisionNames) {
                $g->division_name = ($divisionNames[$g->Division_id] ?? '') . ($divisionNames[$g->Division_id] ? ': ' : '') . ($g->Name ?? 'Group ' . $g->id);
                return $g;
            });
        $returnUrl = $request->input('return') ?: route('manage.brackets.show', [$tid, (int) $currentGroup->Division_id, (int) $currentGroup->id]);

        return view('manage.brackets.move-wrestler', [
            'tournament' => $tournament,
            'wrestler' => $tw,
            'division' => $division,
            'groups' => $groups,
            'returnUrl' => $returnUrl,
        ]);
    }

    /**
     * Move wrestler to another group (any division). If already bracketed, remove from old bracket
     * and add to a new bracket in the target group's division.
     */
    public function moveWrestlerToGroup(Request $request, int $tid, int $wid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $groupInput = (string) $request->input('group_id');
        if ($groupInput === '') {
            return redirect()->back()->with('error', 'Please select a group.');
        }

        $targetGroup = null;
        if (str_contains($groupInput, '_')) {
            [$targetDid, $targetGid] = explode('_', $groupInput, 2);
            $targetGroup = DivGroup::where('id', (int) $targetGid)->where('Tournament_Id', $tid)->where('Division_id', (int) $targetDid)->first();
        } else {
            $targetGroup = DivGroup::where('id', (int) $groupInput)->where('Tournament_Id', $tid)->first();
        }
        if (! $targetGroup) {
            return redirect()->back()->with('error', 'Invalid group.');
        }

        $tw = TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();
        $wrestler = Wrestler::find($tw->Wrestler_Id);
        if (! DivGroup::acceptsWrestlerProfileGender($wrestler->wr_gender ?? null, $targetGroup->gender)) {
            return redirect()->back()->with('error', 'Boys cannot be moved to a girls-only group.');
        }
        $currentGroup = DivGroup::where('id', $tw->group_id)->where('Tournament_Id', $tid)->first();
        if ($currentGroup && (int) $currentGroup->id === (int) $targetGroup->id && (int) $currentGroup->Division_id === (int) $targetGroup->Division_id) {
            return redirect()->back()->with('info', 'Wrestler is already in that group.');
        }

        $wasBracketed = (bool) $tw->bracketed;
        $oldBracketId = (int) $tw->wr_bracket_id;

        if ($wasBracketed && $oldBracketId >= 1) {
            Bracket::where('id', $oldBracketId)->where('wr_Id', $wid)->where('Tournament_Id', $tid)->delete();
            $this->renumberBracketPositions($tid, $oldBracketId);
        }

        $tw->group_id = $targetGroup->id;
        $tw->division_id = $targetGroup->Division_id;
        if ($wasBracketed) {
            $newBracketId = (int) Bracket::where('Tournament_Id', $tid)->max('id') + 1;
            if ($newBracketId < 1) {
                $newBracketId = 1;
            }
            Bracket::insert([
                'id' => $newBracketId,
                'wr_Id' => $wid,
                'wr_pos' => 0,
                'bouted' => 0,
                'Tournament_Id' => $tid,
                'Division_Id' => $targetGroup->Division_id,
                'printed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $tw->wr_bracket_id = $newBracketId;
            $tw->wr_bracket_position = 0;
            $tw->bracketed = 1;
        }
        $tw->save();

        $returnUrl = $request->input('return');
        if ($returnUrl && str_starts_with($returnUrl, url(''))) {
            return redirect($returnUrl)->with('success', 'Wrestler moved to ' . ($targetGroup->Name ?? 'group ' . $targetGroup->id) . ($wasBracketed ? ' and placed in a new bracket.' : '.'));
        }
        return redirect()->route('manage.brackets.show', [$tid, (int) $targetGroup->Division_id, (int) $targetGroup->id])->with('success', 'Wrestler moved to ' . ($targetGroup->Name ?? 'group') . ($wasBracketed ? ' and placed in a new bracket.' : '.'));
    }

    /**
     * Move a wrestler to the last position within their current bracket. Others shift down.
     */
    public function moveWrestlerToLast(Request $request, int $tid, int $wid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $tw = TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();
        $bracketId = (int) $tw->wr_bracket_id;
        if ($bracketId < 1) {
            return redirect()->back()->with('error', 'Wrestler is not in a bracket.');
        }

        $rows = Bracket::where('id', $bracketId)->where('Tournament_Id', $tid)->orderBy('wr_pos')->get();
        if ($rows->count() <= 1) {
            return redirect()->back()->with('info', 'Wrestler is already the only one in the bracket.');
        }

        $ordered = $rows->pluck('wr_Id')->values()->all();
        $idx = array_search((int) $wid, $ordered, true);
        if ($idx === false) {
            return redirect()->back()->with('error', 'Wrestler not found in bracket.');
        }
        // New order: everyone before stays, everyone after shifts down, this wrestler at end
        $before = array_slice($ordered, 0, $idx);
        $after = array_slice($ordered, $idx + 1);
        $newOrder = array_merge($before, $after, [$wid]);

        foreach ($newOrder as $pos => $wrId) {
            Bracket::where('id', $bracketId)->where('wr_Id', $wrId)->where('Tournament_Id', $tid)->update(['wr_pos' => $pos]);
            TournamentWrestler::where('id', $wrId)->where('Tournament_id', $tid)->update(['wr_bracket_position' => $pos]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Wrestler moved to last position.']);
        }
        return redirect()->back()->with('success', 'Wrestler moved to last position in bracket.');
    }

    /**
     * Renumber wr_pos to 0,1,2,... for all rows in a bracket and update tournamentwrestlers.
     */
    private function renumberBracketPositions(int $tid, int $bracketId): void
    {
        $rows = Bracket::where('id', $bracketId)->where('Tournament_Id', $tid)->orderBy('wr_pos')->get();
        $pos = 0;
        foreach ($rows as $row) {
            Bracket::where('id', $bracketId)->where('wr_Id', $row->wr_Id)->where('Tournament_Id', $tid)->update(['wr_pos' => $pos]);
            TournamentWrestler::where('id', $row->wr_Id)->where('Tournament_id', $tid)->update(['wr_bracket_position' => $pos]);
            $pos++;
        }
    }

    private function perBracketForGroup(string $bracketType, int $divisionPerBracket): int
    {
        if (is_numeric(trim($bracketType))) {
            return (int) $bracketType;
        }
        return $divisionPerBracket ?: 6;
    }
}

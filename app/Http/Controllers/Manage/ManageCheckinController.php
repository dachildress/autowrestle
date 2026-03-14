<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManageCheckinController extends Controller
{
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
     * List divisions and groups for check-in; redirect to first group if only one.
     */
    public function index(Request $request, int $tid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load(['divisions.divGroups', 'divisions']);
        $groups = [];
        foreach ($tournament->divisions as $div) {
            foreach ($div->divGroups as $g) {
                $groups[] = (object) [
                    'id' => $g->id,
                    'Name' => $g->Name,
                    'division_id' => $div->id,
                    'division_name' => $div->DivisionName,
                ];
            }
        }
        if ($groups === []) {
            return view('manage.checkin.index', ['tournament' => $tournament, 'groups' => [], 'selected' => null, 'wrestlers' => collect()]);
        }
        $first = $groups[0];
        return redirect()->route('manage.checkin.show', [$tid, $first->division_id, $first->id]);
    }

    /**
     * Remove all wrestlers who are not checked in (before bouting).
     */
    public function clearUnchecked(Request $request, int $tid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $deleted = TournamentWrestler::where('Tournament_id', $tid)
            ->where('checked_in', false)
            ->delete();
        return redirect()->back()->with('success', $deleted ? "Removed {$deleted} unchecked wrestler(s)." : 'No unchecked wrestlers to remove.');
    }

    /**
     * Remove unchecked wrestlers (no-shows) for a single division.
     */
    public function clearUncheckedDivision(Request $request, int $tid, int $did): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $groupIds = DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->pluck('id');
        $deleted = TournamentWrestler::where('Tournament_id', $tid)
            ->whereIn('group_id', $groupIds)
            ->where('checked_in', false)
            ->delete();
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->first();
        $divisionName = $division ? $division->DivisionName : 'division';
        return redirect()->back()->with('success', $deleted ? "Removed {$deleted} unchecked wrestler(s) from {$divisionName}." : "No unchecked wrestlers to remove from {$divisionName}.");
    }

    /**
     * Show wrestlers for a group (scoped by division so group id is unambiguous).
     */
    public function show(Request $request, int $tid, int $did, int $gid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->firstOrFail();

        $wrestlers = TournamentWrestler::query()
            ->join('divgroups', function ($j) use ($tid, $did, $gid) {
                $j->on('tournamentwrestlers.group_id', '=', 'divgroups.id')
                    ->where('divgroups.Tournament_Id', '=', $tid)
                    ->where('divgroups.Division_id', '=', $did)
                    ->where('divgroups.id', '=', $gid);
            })
            ->where('tournamentwrestlers.Tournament_id', $tid)
            ->select('tournamentwrestlers.*')
            ->orderBy('tournamentwrestlers.wr_weight')
            ->orderBy('tournamentwrestlers.wr_grade')
            ->get();

        $tournament->load(['divisions.divGroups']);
        $groups = [];
        foreach ($tournament->divisions as $div) {
            foreach ($div->divGroups as $g) {
                $groups[] = (object) [
                    'id' => $g->id,
                    'Name' => $g->Name,
                    'division_id' => $div->id,
                    'division_name' => $div->DivisionName,
                ];
            }
        }

        return view('manage.checkin.show', [
            'tournament' => $tournament,
            'division' => $division,
            'group' => $group,
            'groups' => $groups,
            'selected_did' => $did,
            'selected_gid' => $gid,
            'wrestlers' => $wrestlers,
        ]);
    }

    /**
     * Toggle check-in for one wrestler or all (id = 'all').
     */
    public function update(Request $request, int $tid, string $id, int $value): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $value = $value ? 1 : 0;
        if ($id === 'all') {
            TournamentWrestler::where('Tournament_id', $tid)->update(['checked_in' => $value]);
            return redirect()->back()->with('success', $value ? 'All marked checked in.' : 'All marked not checked in.');
        }
        $wid = (int) $id;
        TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->update(['checked_in' => $value]);
        return redirect()->back()->with('success', 'Check-in updated.');
    }

    /**
     * Print check-in sheet for a division.
     */
    public function printDivision(Request $request, int $tid, int $did): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();

        $wrestlers = TournamentWrestler::query()
            ->join('divgroups', 'tournamentwrestlers.group_id', '=', 'divgroups.id')
            ->where('divgroups.Tournament_Id', $tid)
            ->where('divgroups.Division_id', $did)
            ->where('tournamentwrestlers.Tournament_id', $tid)
            ->select('tournamentwrestlers.*')
            ->orderBy('tournamentwrestlers.wr_last_name')
            ->orderBy('tournamentwrestlers.wr_grade')
            ->orderBy('tournamentwrestlers.wr_weight')
            ->get();

        return view('manage.checkin.print', [
            'tournament' => $tournament,
            'division' => $division,
            'wrestlers' => $wrestlers,
            'count' => $wrestlers->count(),
        ]);
    }
}

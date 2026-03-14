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

/**
 * View Groups: show groups as tabs and wrestlers per group (View -> View Groups).
 */
class ViewGroupsController extends Controller
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
     * Redirect to first division's first group.
     */
    public function index(Request $request, int $tid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load(['divisions' => fn ($q) => $q->orderBy('id'), 'divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $tid)->orderBy('id')]);

        $firstDivision = $tournament->divisions->first();
        if (! $firstDivision || $firstDivision->divGroups->isEmpty()) {
            return view('manage.viewgroups.index', [
                'tournament' => $tournament,
                'divisions' => $tournament->divisions,
            ]);
        }

        $firstGroup = $firstDivision->divGroups->first();
        return redirect()->route('manage.viewgroups.show', [$tid, $firstDivision->id, $firstGroup->id]);
    }

    /**
     * Show all groups (all divisions) as tabs and wrestlers for the selected group.
     */
    public function show(Request $request, int $tid, int $did, int $gid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->firstOrFail();

        $tournament->load(['divisions' => fn ($q) => $q->orderBy('id'), 'divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $tid)->orderBy('id')]);

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
            ->orderBy('tournamentwrestlers.wr_last_name')
            ->get();

        // All groups across all divisions for the tabs
        $allGroups = [];
        foreach ($tournament->divisions as $d) {
            foreach ($d->divGroups as $g) {
                $allGroups[] = (object) [
                    'did' => $d->id,
                    'gid' => $g->id,
                    'name' => $g->Name,
                    'division_name' => $d->DivisionName,
                ];
            }
        }

        return view('manage.viewgroups.show', [
            'tournament' => $tournament,
            'division' => $division,
            'group' => $group,
            'allGroups' => $allGroups,
            'wrestlers' => $wrestlers,
        ]);
    }

    /**
     * Show form for tournament director to edit a wrestler's info (tournament registration).
     */
    public function editWrestler(Request $request, int $tid, int $wid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tw = TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();
        $did = null;
        $gid = $tw->group_id;
        if ($gid) {
            $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->first();
            $did = $group ? $group->Division_id : null;
        }
        return view('manage.viewgroups.edit-wrestler', [
            'tournament' => $tournament,
            'tw' => $tw,
            'did' => $did,
            'gid' => $gid,
            'returnUrl' => $request->query('return'),
        ]);
    }

    /**
     * Update a tournament wrestler's info (tournament director).
     */
    public function updateWrestler(Request $request, int $tid, int $wid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $tw = TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();

        $validated = $request->validate([
            'wr_first_name' => 'required|string|max:30',
            'wr_last_name' => 'required|string|max:30',
            'wr_club' => 'required|string|max:30',
            'wr_age' => 'required|integer|min:3|max:19',
            'wr_grade' => 'required|string|max:10',
            'wr_weight' => 'nullable|numeric|min:0|max:500',
            'wr_years' => 'required|integer|min:0|max:30',
        ]);

        $tw->wr_first_name = $validated['wr_first_name'];
        $tw->wr_last_name = $validated['wr_last_name'];
        $tw->wr_club = $validated['wr_club'];
        $tw->wr_age = (int) $validated['wr_age'];
        $tw->wr_grade = $validated['wr_grade'];
        $tw->wr_weight = isset($validated['wr_weight']) && $validated['wr_weight'] !== '' && $validated['wr_weight'] !== null ? (int) $validated['wr_weight'] : null;
        $tw->wr_years = (int) $validated['wr_years'];
        $tw->wr_pr = (int) ($tw->wr_weight ?? 0);
        $tw->save();

        $did = $tw->group_id ? (int) DivGroup::where('id', $tw->group_id)->where('Tournament_Id', $tid)->value('Division_id') : null;
        $gid = $tw->group_id;

        $return = $request->input('return');
        if ($return) {
            $base = $request->getSchemeAndHttpHost();
            if (str_starts_with($return, $base) || str_starts_with($return, '/')) {
                return redirect($return)->with('success', 'Wrestler updated.');
            }
        }
        if ($did && $gid) {
            return redirect()->route('manage.viewgroups.show', [$tid, $did, $gid])->with('success', 'Wrestler updated.');
        }
        return redirect()->route('manage.viewgroups.index', $tid)->with('success', 'Wrestler updated.');
    }
}

<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\Wrestler;
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
        $divisions = Division::where('Tournament_Id', $tid)->orderBy('id')->get();

        foreach ($divisions as $division) {
            $groups = DivGroup::where('Tournament_Id', $tid)->where('Division_id', $division->id)->orderBy('id')->get();
            if ($groups->isNotEmpty()) {
                return redirect()->route('manage.viewgroups.show', [$tid, $division->id, $groups->first()->id]);
            }
        }

        return view('manage.viewgroups.index', [
            'tournament' => $tournament,
            'divisions' => $divisions,
        ]);
    }

    /**
     * Show all groups (all divisions) as tabs and wrestlers for the selected group.
     */
    public function show(Request $request, int $tid, int $did, int $gid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->firstOrFail();

        $divisions = Division::where('Tournament_Id', $tid)->orderBy('id')->get();
        $allGroups = [];
        foreach ($divisions as $d) {
            foreach (DivGroup::where('Tournament_Id', $tid)->where('Division_id', $d->id)->orderBy('id')->get() as $g) {
                $allGroups[] = (object) [
                    'did' => $d->id,
                    'gid' => $g->id,
                    'name' => $g->display_name,
                    'division_name' => $d->DivisionName,
                ];
            }
        }

        // When same group id exists in multiple divisions, wrestlers with division_id set show only in that division.
        // Wrestlers with division_id null (legacy/unbackfilled) show in the "canonical" division for this group (smallest Division_id).
        $canonicalDivisionIdForGroup = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->min('Division_id');
        $isCanonicalDivision = $canonicalDivisionIdForGroup !== null && (int) $did === (int) $canonicalDivisionIdForGroup;

        $query = TournamentWrestler::query()
            ->with('wrestler')
            ->join('divgroups', function ($j) use ($tid, $did, $gid) {
                $j->on('tournamentwrestlers.group_id', '=', 'divgroups.id')
                    ->where('divgroups.Tournament_Id', '=', $tid)
                    ->where('divgroups.Division_id', '=', $did)
                    ->where('divgroups.id', '=', $gid);
            })
            ->where('tournamentwrestlers.Tournament_id', $tid)
            ->where('tournamentwrestlers.group_id', $gid);

        if ($isCanonicalDivision) {
            $query->where(function ($q) use ($did) {
                $q->where('tournamentwrestlers.division_id', $did)
                    ->orWhereNull('tournamentwrestlers.division_id');
            });
        } else {
            $query->where('tournamentwrestlers.division_id', $did);
        }

        $wrestlers = $query
            ->select('tournamentwrestlers.*')
            ->orderBy('tournamentwrestlers.wr_weight')
            ->orderBy('tournamentwrestlers.wr_last_name')
            ->get();

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
     * Passes only groups that match the wrestler's gender (from base Wrestler profile).
     */
    public function editWrestler(Request $request, int $tid, int $wid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tw = TournamentWrestler::with('wrestler')->where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();
        $did = null;
        $gid = $tw->group_id;
        if ($gid) {
            $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->first();
            $did = $group ? $group->Division_id : null;
        }
        $wrestler = Wrestler::find($tw->Wrestler_Id);
        $wrestlerGender = ($wrestler && $wrestler->wr_gender === 'Girl') ? 'girls' : 'boys';
        $allowedGenders = $wrestlerGender === 'girls' ? ['girls', 'coed'] : ['boys', 'coed'];
        $divisionNames = Division::where('Tournament_Id', $tid)->pluck('DivisionName', 'id');
        $allowedGroups = DivGroup::where('Tournament_Id', $tid)
            ->whereIn('gender', $allowedGenders)
            ->orderBy('Division_id')
            ->orderBy('Name')
            ->get()
            ->map(function ($g) use ($divisionNames) {
                $g->division_name = ($divisionNames[$g->Division_id] ?? '') . ($divisionNames[$g->Division_id] ? ': ' : '') . $g->display_name;
                return $g;
            });
        return view('manage.viewgroups.edit-wrestler', [
            'tournament' => $tournament,
            'tw' => $tw,
            'did' => $did,
            'gid' => $gid,
            'allowedGroups' => $allowedGroups,
            'returnUrl' => $request->query('return'),
        ]);
    }

    /**
     * Update a tournament wrestler's info (tournament director).
     * If group_id is provided, validates that the group matches the wrestler's gender.
     */
    public function updateWrestler(Request $request, int $tid, int $wid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $tw = TournamentWrestler::with('wrestler')->where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();

        $validated = $request->validate([
            'wr_first_name' => 'required|string|max:30',
            'wr_last_name' => 'required|string|max:30',
            'wr_club' => 'required|string|max:30',
            'wr_age' => 'required|integer|min:3|max:19',
            'wr_grade' => 'required|string|max:10',
            'wr_weight' => 'nullable|numeric|min:0|max:500',
            'wr_years' => 'required|integer|min:0|max:30',
            'group_id' => 'nullable|integer',
        ]);

        $tw->wr_first_name = $validated['wr_first_name'];
        $tw->wr_last_name = $validated['wr_last_name'];
        $tw->wr_club = $validated['wr_club'];
        $tw->wr_age = (int) $validated['wr_age'];
        $tw->wr_grade = $validated['wr_grade'];
        $tw->wr_weight = isset($validated['wr_weight']) && $validated['wr_weight'] !== '' && $validated['wr_weight'] !== null ? (int) $validated['wr_weight'] : null;
        $tw->wr_years = (int) $validated['wr_years'];
        $tw->wr_pr = (int) ($tw->wr_weight ?? 0);

        if (! empty($validated['group_id'])) {
            $newGroup = DivGroup::where('id', $validated['group_id'])->where('Tournament_Id', $tid)->first();
            if ($newGroup) {
                $wrestler = Wrestler::find($tw->Wrestler_Id);
                $wrestlerGender = ($wrestler && $wrestler->wr_gender === 'Girl') ? 'girls' : 'boys';
                $allowedGenders = $wrestlerGender === 'girls' ? ['girls', 'coed'] : ['boys', 'coed'];
                if (in_array((string) $newGroup->gender, $allowedGenders, true)) {
                    $tw->group_id = $newGroup->id;
                    $tw->division_id = $newGroup->Division_id;
                }
            }
        }

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

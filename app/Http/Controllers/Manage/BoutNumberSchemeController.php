<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\BoutNumberScheme;
use App\Models\BoutNumberSchemeGroup;
use App\Models\BoutSetting;
use App\Models\Division;
use App\Models\Tournament;
use App\Models\TournamentMat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoutNumberSchemeController extends Controller
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

    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load('tournamentMats');
        $schemes = BoutNumberScheme::where('tournament_id', $tid)->orderBy('scheme_name')->get();
        $hasMats = $tournament->tournamentMats->isNotEmpty();
        $hasDivisionsAndGroups = $tournament->divisions()->exists()
            && $tournament->divisions()->whereHas('divGroups', fn ($q) => $q->where('Tournament_Id', $tid))->exists();
        return view('manage.number-schemes.index', compact('tournament', 'schemes', 'hasMats', 'hasDivisionsAndGroups'));
    }

    public function create(Request $request, int $tid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load(['divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $tid)->orderBy('MinGrade')->orderBy('id'), 'tournamentMats']);
        $mats = $tournament->tournamentMats;
        if ($mats->isEmpty()) {
            return redirect()->route('manage.number-schemes.index', $tid)
                ->with('error', 'Add at least one mat in Mat setup before creating a number scheme.');
        }
        $hasGroups = $tournament->divisions->contains(fn ($d) => $d->divGroups->isNotEmpty());
        if (! $hasGroups) {
            return redirect()->route('manage.number-schemes.index', $tid)
                ->with('error', 'Add divisions and groups (Edit Divisions) before creating a number scheme.');
        }
        $rounds = BoutSetting::select('Round')->distinct()->orderBy('Round')->pluck('Round')->values()->all();
        if (empty($rounds)) {
            $rounds = [1, 2, 3, 4, 5];
        }
        return view('manage.number-schemes.create', compact('tournament', 'mats', 'rounds'));
    }

    public function store(Request $request, int $tid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $allowedMatNumbers = $tournament->tournamentMats()->pluck('mat_number')->all();
        if (empty($allowedMatNumbers)) {
            return redirect()->route('manage.number-schemes.index', $tid)
                ->with('error', 'Add at least one mat in Mat setup before creating a number scheme.');
        }
        $valid = $request->validate([
            'scheme_name' => 'required|string|max:100',
            'start_at' => 'required|integer|min:1|max:9999',
            'skip_byes' => 'required|in:0,1',
            'match_ids' => 'nullable|string|max:500',
            'all_mats' => 'required|in:0,1',
            'all_groups' => 'required|in:0,1',
            'all_rounds' => 'required|in:0,1',
            'mat_numbers' => 'nullable|array',
            'mat_numbers.*' => 'integer|min:1|max:255',
            'round_numbers' => 'nullable|array',
            'round_numbers.*' => 'integer|min:1|max:10',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'string',
            'same_mat_per_bracket' => 'nullable|in:0,1',
        ]);

        if ((int) $valid['all_mats'] === 0 && (empty($valid['mat_numbers']) || ! is_array($valid['mat_numbers']))) {
            return redirect()->back()->withInput()->withErrors(['mat_numbers' => 'Select at least one mat or choose All Mats.']);
        }
        if ((int) $valid['all_mats'] === 0 && ! empty($valid['mat_numbers'])) {
            $invalid = array_diff(array_map('intval', $valid['mat_numbers']), $allowedMatNumbers);
            if (! empty($invalid)) {
                return redirect()->back()->withInput()->withErrors(['mat_numbers' => 'Selected mat numbers must be from the tournament mat setup.']);
            }
        }
        if ((int) $valid['all_groups'] === 0 && (empty($valid['group_ids']) || ! is_array($valid['group_ids']))) {
            return redirect()->back()->withInput()->withErrors(['group_ids' => 'Select at least one group or choose All Groups.']);
        }
        if ((int) $valid['all_rounds'] === 0 && (empty($valid['round_numbers']) || ! is_array($valid['round_numbers']))) {
            return redirect()->back()->withInput()->withErrors(['round_numbers' => 'Select at least one round or choose All Rounds.']);
        }

        $scheme = BoutNumberScheme::create([
            'tournament_id' => $tid,
            'scheme_name' => $valid['scheme_name'],
            'start_at' => (int) $valid['start_at'],
            'skip_byes' => (int) $valid['skip_byes'] === 1,
            'match_ids' => $valid['match_ids'] ?? null,
            'all_mats' => (int) $valid['all_mats'] === 1,
            'all_groups' => (int) $valid['all_groups'] === 1,
            'all_rounds' => (int) $valid['all_rounds'] === 1,
            'mat_numbers' => (int) $valid['all_mats'] === 0 ? array_values(array_map('intval', $valid['mat_numbers'])) : null,
            'round_numbers' => (int) $valid['all_rounds'] === 0 ? array_values(array_map('intval', $valid['round_numbers'])) : null,
            'same_mat_per_bracket' => ! empty($valid['same_mat_per_bracket']),
        ]);

        if ((int) $valid['all_groups'] === 0 && ! empty($valid['group_ids'])) {
            foreach ($valid['group_ids'] as $key) {
                $parts = explode(',', $key, 2);
                if (count($parts) === 2) {
                    BoutNumberSchemeGroup::create([
                        'bout_number_scheme_id' => $scheme->id,
                        'tournament_id' => $tid,
                        'division_id' => (int) $parts[0],
                        'group_id' => (int) $parts[1],
                    ]);
                }
            }
        }

        return redirect()->route('manage.number-schemes.index', $tid)->with('success', 'Number scheme "' . $scheme->scheme_name . '" added.');
    }

    public function edit(Request $request, int $tid, int $sid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $scheme = BoutNumberScheme::where('id', $sid)->where('tournament_id', $tid)->firstOrFail();
        $scheme->load('schemeGroups');
        $tournament->load(['divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $tid)->orderBy('MinGrade')->orderBy('id'), 'tournamentMats']);
        $mats = $tournament->tournamentMats;
        if ($mats->isEmpty()) {
            return redirect()->route('manage.number-schemes.index', $tid)
                ->with('error', 'Tournament has no mats defined. Add mats in Mat setup to edit schemes.');
        }
        $rounds = BoutSetting::select('Round')->distinct()->orderBy('Round')->pluck('Round')->values()->all();
        if (empty($rounds)) {
            $rounds = [1, 2, 3, 4, 5];
        }
        return view('manage.number-schemes.edit', compact('tournament', 'scheme', 'mats', 'rounds'));
    }

    public function update(Request $request, int $tid, int $sid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $scheme = BoutNumberScheme::where('id', $sid)->where('tournament_id', $tid)->firstOrFail();
        $allowedMatNumbers = $tournament->tournamentMats()->pluck('mat_number')->all();

        $valid = $request->validate([
            'scheme_name' => 'required|string|max:100',
            'start_at' => 'required|integer|min:1|max:9999',
            'skip_byes' => 'required|in:0,1',
            'match_ids' => 'nullable|string|max:500',
            'all_mats' => 'required|in:0,1',
            'all_groups' => 'required|in:0,1',
            'all_rounds' => 'required|in:0,1',
            'mat_numbers' => 'nullable|array',
            'mat_numbers.*' => 'integer|min:1|max:255',
            'round_numbers' => 'nullable|array',
            'round_numbers.*' => 'integer|min:1|max:10',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'string',
            'same_mat_per_bracket' => 'nullable|in:0,1',
        ]);

        if ((int) $valid['all_mats'] === 0 && (empty($valid['mat_numbers']) || ! is_array($valid['mat_numbers']))) {
            return redirect()->back()->withInput()->withErrors(['mat_numbers' => 'Select at least one mat or choose All Mats.']);
        }
        if ((int) $valid['all_mats'] === 0 && ! empty($valid['mat_numbers']) && ! empty($allowedMatNumbers)) {
            $invalid = array_diff(array_map('intval', $valid['mat_numbers']), $allowedMatNumbers);
            if (! empty($invalid)) {
                return redirect()->back()->withInput()->withErrors(['mat_numbers' => 'Selected mat numbers must be from the tournament mat setup.']);
            }
        }
        if ((int) $valid['all_groups'] === 0 && (empty($valid['group_ids']) || ! is_array($valid['group_ids']))) {
            return redirect()->back()->withInput()->withErrors(['group_ids' => 'Select at least one group or choose All Groups.']);
        }
        if ((int) $valid['all_rounds'] === 0 && (empty($valid['round_numbers']) || ! is_array($valid['round_numbers']))) {
            return redirect()->back()->withInput()->withErrors(['round_numbers' => 'Select at least one round or choose All Rounds.']);
        }

        $scheme->update([
            'scheme_name' => $valid['scheme_name'],
            'start_at' => (int) $valid['start_at'],
            'skip_byes' => (int) $valid['skip_byes'] === 1,
            'match_ids' => $valid['match_ids'] ?? null,
            'all_mats' => (int) $valid['all_mats'] === 1,
            'all_groups' => (int) $valid['all_groups'] === 1,
            'all_rounds' => (int) $valid['all_rounds'] === 1,
            'mat_numbers' => (int) $valid['all_mats'] === 0 ? array_values(array_map('intval', $valid['mat_numbers'])) : null,
            'round_numbers' => (int) $valid['all_rounds'] === 0 ? array_values(array_map('intval', $valid['round_numbers'])) : null,
            'same_mat_per_bracket' => ! empty($valid['same_mat_per_bracket']),
        ]);

        BoutNumberSchemeGroup::where('bout_number_scheme_id', $scheme->id)->delete();
        if ((int) $valid['all_groups'] === 0 && ! empty($valid['group_ids'])) {
            foreach ($valid['group_ids'] as $key) {
                $parts = explode(',', $key, 2);
                if (count($parts) === 2) {
                    BoutNumberSchemeGroup::create([
                        'bout_number_scheme_id' => $scheme->id,
                        'tournament_id' => $tid,
                        'division_id' => (int) $parts[0],
                        'group_id' => (int) $parts[1],
                    ]);
                }
            }
        }

        return redirect()->route('manage.number-schemes.index', $tid)->with('success', 'Number scheme "' . $scheme->scheme_name . '" updated.');
    }

    public function destroy(Request $request, int $tid, int $sid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $scheme = BoutNumberScheme::where('id', $sid)->where('tournament_id', $tid)->firstOrFail();
        $name = $scheme->scheme_name;
        $scheme->delete();
        return redirect()->route('manage.number-schemes.index', $tid)->with('success', 'Number scheme "' . $name . '" deleted.');
    }
}

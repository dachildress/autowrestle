<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManageGroupController extends Controller
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

    public function create(Request $request, int $tid, int $did): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        return view('manage.groups.create', [
            'tournament' => $tournament,
            'division' => $division,
        ]);
    }

    public function store(Request $request, int $tid, int $did): RedirectResponse|View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $validated = $request->validate([
            'Name' => 'required|string|max:25',
            'MinAge' => 'required|integer|min:3|max:19',
            'MaxAge' => 'required|integer|min:3|max:19',
            'MinGrade' => 'required|integer|min:-1|max:12',
            'MaxGrade' => 'required|integer|min:-1|max:12',
            'MaxWeightDiff' => 'required|integer|min:0|max:20',
            'MaxPwrDiff' => 'required|integer|min:0|max:30',
            'MaxExpDiff' => 'required|integer|min:0|max:30',
            'BracketType' => 'nullable|string|max:20',
        ]);
        $validated['Tournament_Id'] = $tid;
        $validated['Division_id'] = $did;
        $nextId = (int) DivGroup::where('Tournament_Id', $tid)->where('Division_id', $did)->max('id') + 1;
        if ($nextId < 1) {
            $nextId = 1;
        }
        $validated['id'] = $nextId;
        if (empty($validated['BracketType'])) {
            $validated['BracketType'] = 'Round Robin';
        }
        DivGroup::create($validated);
        return redirect()->route('manage.divisions.show', [$tid, $did])->with('success', 'Group added.');
    }

    public function edit(Request $request, int $tid, int $did, int $gid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->firstOrFail();
        return view('manage.groups.edit', [
            'tournament' => $tournament,
            'division' => $division,
            'group' => $group,
        ]);
    }

    public function update(Request $request, int $tid, int $did, int $gid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->firstOrFail();
        $validated = $request->validate([
            'Name' => 'required|string|max:25',
            'MinAge' => 'required|integer|min:3|max:19',
            'MaxAge' => 'required|integer|min:3|max:19',
            'MinGrade' => 'required|integer|min:-1|max:12',
            'MaxGrade' => 'required|integer|min:-1|max:12',
            'MaxWeightDiff' => 'required|integer|min:0|max:20',
            'MaxPwrDiff' => 'required|integer|min:0|max:30',
            'MaxExpDiff' => 'required|integer|min:0|max:30',
            'BracketType' => 'nullable|string|max:20',
        ]);
        if (empty($validated['BracketType'])) {
            $validated['BracketType'] = $group->BracketType ?? 'Round Robin';
        }
        DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->update($validated);
        return redirect()->route('manage.divisions.show', [$tid, $did])->with('success', 'Group updated.');
    }

    public function destroy(Request $request, int $tid, int $did, int $gid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->firstOrFail();
        $bouts = Bout::where('Division_Id', $did)->exists();
        $brackets = Bracket::where('Division_Id', $did)->exists();
        if ($bouts || $brackets) {
            return redirect()->route('manage.divisions.show', [$tid, $did])
                ->with('error', 'The group could not be deleted. Brackets or bouts exist.');
        }
        DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->where('Division_id', $did)->delete();
        return redirect()->route('manage.divisions.show', [$tid, $did])->with('success', 'Group deleted.');
    }
}

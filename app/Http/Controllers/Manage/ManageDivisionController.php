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

class ManageDivisionController extends Controller
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
        $divisions = Division::where('Tournament_Id', $tid)->orderBy('id')->get();
        return view('manage.divisions.index', [
            'tournament' => $tournament,
            'divisions' => $divisions,
        ]);
    }

    public function create(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        return view('manage.divisions.create', ['tournament' => $tournament]);
    }

    public function store(Request $request, int $tid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $validated = $request->validate([
            'DivisionName' => 'required|string|max:45',
            'StartingMat' => 'required|integer|min:0',
            'TotalMats' => 'required|integer|min:0',
            'PerBracket' => 'required|integer|min:0',
        ]);
        $validated['Tournament_Id'] = $tid;
        Division::create($validated);
        return redirect()->route('manage.divisions.index', $tid)->with('success', 'Division added.');
    }

    public function show(Request $request, int $tid, int $did): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $division->load('divGroups');
        return view('manage.divisions.show', [
            'tournament' => $tournament,
            'division' => $division,
        ]);
    }

    public function edit(Request $request, int $tid, int $did): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        return view('manage.divisions.edit', [
            'tournament' => $tournament,
            'division' => $division,
        ]);
    }

    public function update(Request $request, int $tid, int $did): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $validated = $request->validate([
            'DivisionName' => 'required|string|max:45',
            'StartingMat' => 'required|integer|min:0',
            'TotalMats' => 'required|integer|min:0',
            'PerBracket' => 'required|integer|min:0',
        ]);
        $division->update($validated);
        return redirect()->route('manage.divisions.show', [$tid, $did])->with('success', 'Division updated.');
    }

    public function destroy(Request $request, int $tid, int $did): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $bouts = Bout::where('Division_Id', $did)->exists();
        $brackets = Bracket::where('Division_Id', $did)->exists();
        if ($bouts || $brackets) {
            return redirect()->route('manage.divisions.index', $tid)
                ->with('error', 'The division could not be deleted. Brackets or bouts exist.');
        }
        DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->delete();
        $division->delete();
        return redirect()->route('manage.divisions.index', $tid)->with('success', 'Division deleted.');
    }
}

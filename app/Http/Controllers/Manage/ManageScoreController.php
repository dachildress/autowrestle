<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManageScoreController extends Controller
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
     * Show form to enter a bout number to score.
     */
    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        return view('manage.scoring.index', ['tournament' => $tournament]);
    }

    /**
     * Show the bout and two wrestlers for scoring (POST from index with bout number).
     */
    public function show(Request $request, int $tid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $request->validate(['bout' => 'required']);
        $boutId = (int) $request->input('bout');

        $rows = Bout::where('Tournament_Id', $tid)
            ->where('id', $boutId)
            ->orderBy('Wrestler_Id')
            ->get();

        if ($rows->count() < 2) {
            return back()->withErrors(['bout' => 'Bout could not be found or does not have two wrestlers.']);
        }

        $wr1 = TournamentWrestler::find($rows[0]->Wrestler_Id);
        $wr2 = TournamentWrestler::find($rows[1]->Wrestler_Id);
        if (! $wr1 || ! $wr2) {
            return back()->withErrors(['bout' => 'Bout wrestlers could not be found.']);
        }

        return view('manage.scoring.show', [
            'tournament' => $tournament,
            'boutId' => $boutId,
            'wr1' => $wr1,
            'wr2' => $wr2,
            'boutRows' => $rows,
        ]);
    }

    /**
     * Save score for a bout: update both bout rows with points, pin, wrtime, scored.
     */
    public function update(Request $request, int $tid): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $request->validate([
            'bout_id' => 'required|integer',
            'points1' => 'nullable|numeric|min:0',
            'points2' => 'nullable|numeric|min:0',
            'wintype' => 'nullable|string|max:20',
            'totaltime' => 'nullable|string|max:10',
        ]);

        $boutId = (int) $request->input('bout_id');
        $points1 = (float) $request->input('points1', 0);
        $points2 = (float) $request->input('points2', 0);
        $wintype = $request->input('wintype', 'Points');
        $totaltime = $request->input('totaltime', '0:00');

        $rows = Bout::where('Tournament_Id', $tid)->where('id', $boutId)->orderBy('Wrestler_Id')->get();
        if ($rows->count() < 2) {
            return back()->withErrors(['bout_id' => 'Bout not found.']);
        }

        $pin = (strtolower((string) $wintype) === 'fall' || strtolower((string) $wintype) === '5') ? 1 : 0;

        foreach ($rows as $i => $row) {
            $points = $i === 0 ? $points1 : $points2;
            Bout::where('Tournament_Id', $tid)->where('id', $boutId)->where('Wrestler_Id', $row->Wrestler_Id)->update([
                'points' => $points,
                'pin' => $pin,
                'wrtime' => $totaltime,
                'scored' => 1,
                'completed' => true,
            ]);
        }

        return redirect()->route('manage.scoring.index', $tid)->with('success', 'Score saved for bout #' . $boutId . '.');
    }
}

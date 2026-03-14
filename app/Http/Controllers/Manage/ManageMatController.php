<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManageMatController extends Controller
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
     * Step 1: Select which mat to move bouts from.
     */
    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load('divisions');
        $mats = $tournament->getConfiguredMatNumbers();
        if (empty($mats)) {
            return view('manage.mats.index', [
                'tournament' => $tournament,
                'mats' => [],
                'matsWithBouts' => [],
            ]);
        }
        $matsWithBouts = Bout::where('Tournament_Id', $tid)
            ->whereIn('mat_number', $mats)
            ->select('mat_number')
            ->distinct()
            ->pluck('mat_number')
            ->sort()
            ->values()
            ->all();
        return view('manage.mats.index', [
            'tournament' => $tournament,
            'mats' => $mats,
            'matsWithBouts' => $matsWithBouts,
        ]);
    }

    /**
     * Step 2: Show remaining (not completed) bouts on the selected mat; select which to move and target mat.
     */
    public function fromMat(Request $request, int $tid, int $mat): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $mats = $tournament->getConfiguredMatNumbers();
        if (!in_array($mat, $mats, true)) {
            return redirect()->route('manage.mats.index', $tid)->with('error', 'Invalid mat.');
        }
        $boutRows = Bout::where('Tournament_Id', $tid)
            ->where('mat_number', $mat)
            ->where('completed', false)
            ->orderBy('round')
            ->orderBy('id')
            ->get();
        $boutIds = $boutRows->unique('id')->values()->all();
        $bouts = [];
        foreach ($boutIds as $row) {
            $wrestlers = TournamentWrestler::select('tournamentwrestlers.id as wr_id', 'tournamentwrestlers.wr_first_name', 'tournamentwrestlers.wr_last_name', 'tournamentwrestlers.wr_weight', 'tournamentwrestlers.wr_club', 'brackets.wr_pos', 'bouts.round', 'divisions.DivisionName')
                ->join('bouts', function ($j) use ($tid) {
                    $j->on('tournamentwrestlers.id', '=', 'bouts.Wrestler_Id')
                        ->where('bouts.Tournament_Id', '=', $tid);
                })
                ->join('brackets', function ($j) use ($tid) {
                    $j->on('brackets.wr_Id', '=', 'bouts.Wrestler_Id')
                        ->on('brackets.id', '=', 'bouts.Bracket_Id')
                        ->where('brackets.Tournament_Id', '=', $tid);
                })
                ->leftJoin('divisions', 'bouts.Division_Id', '=', 'divisions.id')
                ->where('bouts.id', $row->id)
                ->where('bouts.Tournament_Id', $tid)
                ->orderBy('brackets.wr_pos')
                ->get();
            if ($wrestlers->count() >= 2) {
                $weightQuery = TournamentWrestler::select(DB::raw('MIN(tournamentwrestlers.wr_weight) as low, MAX(tournamentwrestlers.wr_weight) as high'))
                    ->join('brackets', function ($j) use ($tid) {
                        $j->on('tournamentwrestlers.id', '=', 'brackets.wr_Id')
                            ->where('brackets.Tournament_Id', '=', $tid);
                    })
                    ->where('brackets.id', $row->Bracket_Id)
                    ->where('tournamentwrestlers.Tournament_id', $tid)
                    ->first();
                $bouts[] = (object) [
                    'id' => $row->id,
                    'round' => $wrestlers[0]->round,
                    'division_name' => $wrestlers[0]->DivisionName ?? '–',
                    'wr1' => $wrestlers[0],
                    'wr2' => $wrestlers[1],
                    'weight' => $weightQuery ? ($weightQuery->low . ' - ' . $weightQuery->high) : '–',
                ];
            }
        }
        $targetMats = array_values(array_filter($mats, fn ($m) => $m !== $mat));
        return view('manage.mats.from-mat', [
            'tournament' => $tournament,
            'currentMat' => $mat,
            'bouts' => $bouts,
            'targetMats' => $targetMats,
        ]);
    }

    /**
     * Step 3: Move selected bouts to target mat.
     */
    public function move(Request $request, int $tid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $mats = $tournament->getConfiguredMatNumbers();
        $currentMat = (int) $request->input('current_mat');
        $targetMat = (int) $request->input('target_mat');
        $boutIds = $request->input('bout_ids', []);
        if (!is_array($boutIds)) {
            $boutIds = [];
        }
        $boutIds = array_map('intval', array_filter($boutIds));
        if (!in_array($currentMat, $mats, true) || !in_array($targetMat, $mats, true)) {
            return redirect()->route('manage.mats.index', $tid)->with('error', 'Invalid mat selection.');
        }
        if ($currentMat === $targetMat) {
            return redirect()->route('manage.mats.fromMat', [$tid, $currentMat])->with('error', 'Target mat must be different from current mat.');
        }
        if (empty($boutIds)) {
            return redirect()->route('manage.mats.fromMat', [$tid, $currentMat])->with('error', 'Select at least one bout to move.');
        }
        $validIds = Bout::where('Tournament_Id', $tid)
            ->where('mat_number', $currentMat)
            ->whereIn('id', $boutIds)
            ->distinct()
            ->pluck('id')
            ->all();
        Bout::where('Tournament_Id', $tid)->whereIn('id', $validIds)->update(['mat_number' => $targetMat]);
        $count = count($validIds);
        return redirect()->route('manage.mats.fromMat', [$tid, $currentMat])
            ->with('success', $count . ' bout(s) moved to mat ' . $targetMat . '.');
    }
}

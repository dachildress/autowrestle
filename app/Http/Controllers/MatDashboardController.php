<?php

namespace App\Http\Controllers;

use App\Models\Bout;
use App\Models\BoutScoringState;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MatDashboardController extends Controller
{
    /**
     * Mat-side dashboard (scorer): match list for scorer's mat.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user->isScorer()) {
            abort(403, 'Only scorer users can access the mat dashboard.');
        }

        $matNumber = $user->mat_number;
        $tournament = null;
        $bouts = [];
        $rounds = [];
        $incompleteOnly = $request->boolean('incomplete_only');
        $roundFilter = $request->input('round');

        if ($matNumber !== null) {
            $tid = $user->Tournament_id;
            if ($tid) {
                $tournament = Tournament::find($tid);
            }
            if ($tournament) {
                $completedBoutIds = BoutScoringState::where('tournament_id', $tournament->id)
                    ->where('status', 'completed')
                    ->pluck('bout_id')
                    ->all();

                $query = Bout::where('Tournament_Id', $tournament->id)
                    ->where('mat_number', $matNumber)
                    ->orderBy('round')
                    ->orderBy('id');
                if (!empty($completedBoutIds)) {
                    $query->whereNotIn('id', $completedBoutIds);
                }
                if ($incompleteOnly) {
                    $query->where('completed', false);
                }
                if ($roundFilter !== null && $roundFilter !== '') {
                    $query->where('round', $roundFilter);
                }
                $boutRows = $query->get();
                $boutIds = $boutRows->unique('id')->values();

                foreach ($boutIds as $row) {
                    $wrestlers = TournamentWrestler::select('tournamentwrestlers.id as wr_id', 'tournamentwrestlers.wr_first_name', 'tournamentwrestlers.wr_last_name', 'tournamentwrestlers.wr_weight', 'tournamentwrestlers.wr_club', 'brackets.wr_pos', 'bouts.round', 'bouts.completed', 'divisions.DivisionName')
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
                            'bout_number' => $row->bout_number,
                            'round' => $wrestlers[0]->round,
                            'division_name' => $wrestlers[0]->DivisionName ?? '–',
                            'wr1' => $wrestlers[0],
                            'wr2' => $wrestlers[1],
                            'weight' => $weightQuery ? ($weightQuery->low . ' - ' . $weightQuery->high) : '–',
                            'completed' => (bool) $wrestlers[0]->completed,
                        ];
                    }
                }

                $rounds = Bout::where('Tournament_Id', $tournament->id)
                    ->where('mat_number', $matNumber)
                    ->distinct()
                    ->orderBy('round')
                    ->pluck('round')
                    ->values()
                    ->all();
            }
        }

        return view('mat.dashboard', [
            'user' => $user,
            'matNumber' => $matNumber,
            'tournament' => $tournament,
            'bouts' => $bouts,
            'rounds' => $rounds,
            'incompleteOnly' => $incompleteOnly,
            'roundFilter' => $roundFilter,
        ]);
    }
}

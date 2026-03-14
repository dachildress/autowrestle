<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Services\BoutGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManageBoutController extends Controller
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
     * Create bouts for a division and redirect back.
     */
    public function create(Request $request, int $tid, int $did): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $service = app(BoutGenerationService::class);
        $service->createBoutsForDivision($tid, $did);
        return redirect()->route('manage.tournaments.show', $tid)
            ->with('success', 'Bouts created for ' . $division->DivisionName . '.');
    }

    /**
     * Unbout a division: delete bouts, reset bracket/division/group bouted flags.
     */
    public function unbout(Request $request, int $tid, int $did): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();

        Bout::where('Division_Id', $did)->where('Tournament_Id', $tid)->delete();
        Bracket::where('Division_Id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 0, 'printed' => false]);
        Division::where('id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 0]);
        DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 0]);

        return redirect()->route('manage.tournaments.show', $tid)
            ->with('success', 'Bouts cleared for ' . $division->DivisionName . '.');
    }

    /**
     * Print/display bouts for a division, optionally by round (0 = all rounds).
     */
    public function printBouts(Request $request, int $tid, int $did, int $rid = 0): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();

        $query = Bout::where('Division_Id', $did)
            ->where('Tournament_Id', $tid)
            ->orderBy('round')
            ->orderBy('id');

        if ($rid !== 0) {
            $query->where('round', $rid);
        }

        $boutIds = $query->get()->unique('id')->values();

        $bouts = [];
        foreach ($boutIds as $row) {
            $wrestlers = TournamentWrestler::select('tournamentwrestlers.id as wr_id', 'tournamentwrestlers.wr_first_name', 'tournamentwrestlers.wr_last_name', 'tournamentwrestlers.wr_weight', 'tournamentwrestlers.wr_club', 'brackets.wr_pos', 'bouts.round')
                ->join('bouts', function ($j) use ($tid) {
                    $j->on('tournamentwrestlers.id', '=', 'bouts.Wrestler_Id')
                        ->where('bouts.Tournament_Id', '=', $tid);
                })
                ->join('brackets', function ($j) use ($tid) {
                    $j->on('brackets.wr_Id', '=', 'bouts.Wrestler_Id')
                        ->on('brackets.id', '=', 'bouts.Bracket_Id')
                        ->where('brackets.Tournament_Id', '=', $tid);
                })
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
                    'mat_number' => $row->mat_number,
                    'wr1' => $wrestlers[0],
                    'wr2' => $wrestlers[1],
                    'weight' => $weightQuery ? ($weightQuery->low . ' - ' . $weightQuery->high) : '–',
                ];
            }
        }

        return view('manage.bouts.print', [
            'tournament' => $tournament,
            'division' => $division,
            'bouts' => $bouts,
            'round' => $rid,
        ]);
    }

    /**
     * Division/round selection for printing bouts.
     */
    public function selectPrint(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load('divisions');
        return view('manage.bouts.select-print', ['tournament' => $tournament]);
    }
}

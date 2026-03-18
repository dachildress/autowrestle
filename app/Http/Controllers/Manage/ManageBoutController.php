<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\BoutNumberScheme;
use App\Services\BoutGenerationService;
use App\Services\BoutNumberSchemeService;
use Illuminate\Http\JsonResponse;
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
     * Create bouts for a division using a number scheme. If no scheme applies, redirect with error.
     * Optional query: scheme_id to use a specific scheme; otherwise the first applicable scheme is used.
     * When requested as AJAX (Accept: application/json), returns JSON and does not redirect.
     */
    public function create(Request $request, int $tid, int $did): RedirectResponse|JsonResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $schemeService = app(BoutNumberSchemeService::class);
        $wantsJson = $request->wantsJson() || $request->ajax();

        if (! $schemeService->divisionHasScheme($tid, $did)) {
            $message = 'No number scheme applies to ' . $division->DivisionName . '. Add a scheme under Number Schemes first.';
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('manage.tournaments.show', $tid)->with('error', $message);
        }

        $schemeId = $request->query('scheme_id');
        if ($schemeId !== null) {
            $scheme = BoutNumberScheme::where('id', (int) $schemeId)->where('tournament_id', $tid)->firstOrFail();
            if (! $schemeService->schemeAppliesToDivision($scheme, $tid, $did)) {
                $message = 'The selected scheme does not apply to ' . $division->DivisionName . '.';
                if ($wantsJson) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return redirect()->route('manage.tournaments.show', $tid)->with('error', $message);
            }
        } else {
            $scheme = BoutNumberScheme::where('tournament_id', $tid)->get()->first(fn ($s) => $schemeService->schemeAppliesToDivision($s, $tid, $did));
        }

        $schemeService->runSchemeForDivision($tid, $did, $scheme->id);

        if ($wantsJson) {
            return response()->json(['success' => true, 'division_name' => $division->DivisionName]);
        }
        return redirect()->route('manage.tournaments.show', $tid)
            ->with('success', 'Bouts created for ' . $division->DivisionName . ' using scheme "' . $scheme->scheme_name . '".');
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
                    'bout_number' => $row->bout_number,
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

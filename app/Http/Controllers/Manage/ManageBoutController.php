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
     * Create bouts for a division using bout number schemes that apply to that division.
     *
     * By default, runs every applicable scheme in order (name, id): each scheme defines its own
     * mats, groups, rounds, and bout numbering start. Optional query scheme_id runs only that scheme.
     * When requested as AJAX (Accept: application/json), returns JSON and does not redirect.
     */
    public function create(Request $request, int $tid, int $did): RedirectResponse|JsonResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $schemeService = app(BoutNumberSchemeService::class);
        $wantsJson = $request->wantsJson() || $request->ajax();

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
            $schemeService->runSingleSchemeForDivision($tid, $did, $scheme);
            $schemesUsed = collect([$scheme]);
        } else {
            $schemesUsed = $schemeService->applicableSchemesForDivision($tid, $did);
            if ($schemesUsed->isEmpty()) {
                $message = 'No number scheme applies to ' . $division->DivisionName . '. Add a scheme under Number Schemes first.';
                if ($wantsJson) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return redirect()->route('manage.tournaments.show', $tid)->with('error', $message);
            }
            $schemeService->runAllSchemesForDivision($tid, $did);
        }

        $schemeNames = $schemesUsed->pluck('scheme_name')->implode(', ');

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'division_name' => $division->DivisionName,
                'schemes_used' => $schemesUsed->map(fn (BoutNumberScheme $s) => [
                    'id' => $s->id,
                    'name' => $s->scheme_name,
                ])->values()->all(),
            ]);
        }

        $count = $schemesUsed->count();

        return redirect()->route('manage.tournaments.show', $tid)
            ->with('success', 'Bouts created for ' . $division->DivisionName . ' using '
                . $count . ' number scheme' . ($count === 1 ? '' : 's') . ': ' . $schemeNames . '.');
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

        $schemeService = app(BoutNumberSchemeService::class);
        /** @var list<int> $printMats informational: scheme + division span + mats on bouts (never used to exclude rows) */
        $printMats = $schemeService->resolvePrintMatsForDivision($tid, $did);

        // Two DB rows per logical bout (same id, different Wrestler_Id).
        // Do not filter by mat_number: tournament_mats / all_mats often resolves to only mat 1 and would
        // hide mats 2–3. Include every bout for this division, then sort round → mat → bout #.
        // Do NOT filter by Division_Id when loading both rows later: mismatched Division_Id on one row
        // would otherwise drop the whole bout.
        $idsFromBoutColumn = DB::table('bouts')
            ->where('Tournament_Id', $tid)
            ->where('Division_Id', $did)
            ->when($rid !== 0, fn ($q) => $q->where('round', $rid))
            ->distinct()
            ->pluck('id');

        $idsFromWrestlers = DB::table('bouts as b')
            ->join('tournamentwrestlers as tw', function ($join) use ($tid) {
                $join->on('tw.id', '=', 'b.Wrestler_Id')
                    ->where('tw.Tournament_id', '=', $tid);
            })
            ->where('b.Tournament_Id', $tid)
            ->where('tw.division_id', $did)
            ->when($rid !== 0, fn ($q) => $q->where('b.round', $rid))
            ->distinct()
            ->pluck('b.id');

        $boutIds = $idsFromBoutColumn->merge($idsFromWrestlers)->unique()->sort()->values();

        $bouts = [];
        foreach ($boutIds as $boutId) {
            $boutId = (int) $boutId;

            $boutRows = DB::table('bouts')
                ->where('Tournament_Id', $tid)
                ->where('id', $boutId)
                ->orderBy('Wrestler_Id')
                ->get();

            if ($boutRows->count() < 2) {
                continue;
            }

            $wrIds = $boutRows->pluck('Wrestler_Id')->map(fn ($x) => (int) $x)->unique()->values()->all();
            if (count($wrIds) < 2) {
                continue;
            }

            $tws = TournamentWrestler::query()
                ->whereIn('id', $wrIds)
                ->where('Tournament_id', $tid)
                ->get()
                ->keyBy(fn (TournamentWrestler $tw) => (int) $tw->id);

            if ($tws->count() < 2) {
                continue;
            }

            // Division print: both competitors must belong to this division (handles bad bout.Division_Id on one row).
            $bothInDivision = $tws->every(fn (TournamentWrestler $tw) => (int) $tw->division_id === $did);
            if (! $bothInDivision) {
                continue;
            }

            $firstRow = $boutRows->first();
            $bracketId = (int) $firstRow->Bracket_Id;
            $matNumber = (int) ($boutRows->max('mat_number') ?? 0);

            $round = (int) ($boutRows->max('round') ?? 0);
            $boutNumber = $boutRows->pluck('bout_number')->filter(fn ($n) => $n !== null && $n !== '')->max();

            $positions = Bracket::query()
                ->where('Tournament_Id', $tid)
                ->where('id', $bracketId)
                ->whereIn('wr_Id', $wrIds)
                ->get()
                ->keyBy(fn (Bracket $br) => (int) $br->wr_Id);

            $ordered = collect($wrIds)
                ->map(function (int $wid) use ($tws, $positions) {
                    $tw = $tws->get($wid);
                    if ($tw === null) {
                        return null;
                    }
                    $br = $positions->get($wid);

                    return (object) [
                        'wr_id' => $tw->id,
                        'wr_first_name' => $tw->wr_first_name,
                        'wr_last_name' => $tw->wr_last_name,
                        'wr_weight' => $tw->wr_weight,
                        'wr_club' => $tw->wr_club,
                        // Missing bracket row: still print; sort after real positions, then by id
                        'wr_pos' => $br !== null ? (int) $br->wr_pos : 99,
                    ];
                })
                ->filter()
                ->sort(function ($a, $b) {
                    $pa = (int) $a->wr_pos;
                    $pb = (int) $b->wr_pos;
                    if ($pa !== $pb) {
                        return $pa <=> $pb;
                    }

                    return ((int) $a->wr_id) <=> ((int) $b->wr_id);
                })
                ->values();

            if ($ordered->count() < 2) {
                continue;
            }

            $weightQuery = TournamentWrestler::select(DB::raw('MIN(tournamentwrestlers.wr_weight) as low, MAX(tournamentwrestlers.wr_weight) as high'))
                ->join('brackets', function ($j) use ($tid) {
                    $j->on('tournamentwrestlers.id', '=', 'brackets.wr_Id')
                        ->where('brackets.Tournament_Id', '=', $tid);
                })
                ->where('brackets.id', $bracketId)
                ->where('tournamentwrestlers.Tournament_id', $tid)
                ->first();

            $weightLabel = '–';
            if ($weightQuery && $weightQuery->low !== null && $weightQuery->high !== null) {
                $weightLabel = $weightQuery->low . ' - ' . $weightQuery->high;
            } else {
                $w1 = $ordered[0]->wr_weight;
                $w2 = $ordered[1]->wr_weight;
                if ($w1 !== null && $w2 !== null) {
                    $weightLabel = min((float) $w1, (float) $w2) . ' - ' . max((float) $w1, (float) $w2);
                }
            }

            $bouts[] = (object) [
                'id' => $boutId,
                'bout_number' => $boutNumber,
                'round' => $round,
                'mat_number' => $matNumber,
                'wr1' => $ordered[0],
                'wr2' => $ordered[1],
                'weight' => $weightLabel,
            ];
        }

        // Sheets order: round ascending, then mat (numeric), then bout_number (scheme order), then id.
        usort($bouts, function ($a, $b) {
            $roundCmp = (int) $a->round <=> (int) $b->round;
            if ($roundCmp !== 0) {
                return $roundCmp;
            }
            $matCmp = (int) $a->mat_number <=> (int) $b->mat_number;
            if ($matCmp !== 0) {
                return $matCmp;
            }
            $an = $a->bout_number ?? $a->id;
            $bn = $b->bout_number ?? $b->id;
            if (is_numeric($an) && is_numeric($bn)) {
                return (int) $an <=> (int) $bn;
            }

            return $a->id <=> $b->id;
        });

        $matsOnSheets = collect($bouts)->pluck('mat_number')->unique()->sort()->values()->all();

        return view('manage.bouts.print', [
            'tournament' => $tournament,
            'division' => $division,
            'bouts' => $bouts,
            'round' => $rid,
            'print_mats' => $printMats,
            'mats_on_sheets' => $matsOnSheets,
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

<?php

namespace App\Http\Controllers;

use App\Models\DivGroup;
use App\Models\Bout;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\Wrestler;
use App\Services\BracketReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function __construct(
        private BracketReportingService $reporting
    ) {}

    public function current(Request $request): View
    {
        $hasFilters = $request->filled('name')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('city')
            || $request->filled('state');

        $query = Tournament::where('pending_approval', false);

        if (! $hasFilters) {
            $today = now()->startOfDay();
            $query->whereDate('TournamentDate', '>=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('OpenDate')->orWhereDate('OpenDate', '<=', $today);
                });
        }

        if ($request->filled('name')) {
            $query->where('TournamentName', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('TournamentDate', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('TournamentDate', '<=', $request->input('date_to'));
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->input('city') . '%');
        }
        if ($request->filled('state')) {
            $query->where('state', 'like', '%' . $request->input('state') . '%');
        }

        $tournaments = $query->orderBy('TournamentDate', 'asc')->get();

        return view('tournaments.list', [
            'tournaments' => $tournaments,
            'filters' => [
                'name' => $request->input('name'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
            ],
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->load(['divisions', 'tournamentWrestlers.wrestler']);

        $tab = $request->query('tab', 'information');
        $validTabs = ['information', 'my-wrestlers', 'brackets', 'teams', 'results'];
        if (! in_array($tab, $validTabs, true)) {
            $tab = 'information';
        }

        $registrationOpen = ! $tournament->isPast() && $tournament->isRegistrationOpen();
        $registrationLocked = (int) $tournament->status === 2;

        $userWrestlers = null;
        $statusByWrestler = [];
        if ($request->user()) {
            $userWrestlers = Wrestler::where('user_id', $request->user()->id)
                ->orderBy('wr_last_name')
                ->orderBy('wr_weight')
                ->get();
            $twEntries = TournamentWrestler::where('Tournament_id', $id)
                ->whereIn('Wrestler_Id', $userWrestlers->pluck('id'))
                ->get()
                ->groupBy('Wrestler_Id');
            foreach ($userWrestlers as $w) {
                $entries = $twEntries->get($w->id);
                if ($entries && $entries->contains('bracketed', 1)) {
                    $statusByWrestler[$w->id] = 'locked';
                } elseif ($entries && $entries->isNotEmpty()) {
                    $statusByWrestler[$w->id] = 'withdraw';
                } else {
                    $statusByWrestler[$w->id] = 'add';
                }
            }
        }

        $teamsData = [];
        $teamsDivisionId = $request->query('division_id');
        if ($tab === 'teams') {
            $query = TournamentWrestler::where('Tournament_id', $id);
            if ($teamsDivisionId !== null && $teamsDivisionId !== '') {
                $groupIds = DivGroup::where('Tournament_Id', $id)
                    ->where('Division_id', (int) $teamsDivisionId)
                    ->pluck('id');
                $query->whereIn('group_id', $groupIds);
            }
            $wrestlersByClub = $query->orderBy('wr_club')
                ->orderBy('wr_last_name')
                ->orderBy('wr_first_name')
                ->get()
                ->groupBy('wr_club');
            foreach ($wrestlersByClub as $club => $wrestlers) {
                $teamsData[] = (object) [
                    'club' => $club,
                    'wrestlers' => $wrestlers->map(fn ($w) => (object) [
                        'full_name' => $w->full_name,
                        'wr_weight' => $w->wr_weight,
                    ])->values(),
                ];
            }
        }

        $resultsBrackets = [];
        $resultsDivisionId = $request->query('results_division_id');
        $resultsTeam = $request->query('results_team');
        $teamsForResults = collect();
        if ($tab === 'results') {
            $divisionIdFilter = $resultsDivisionId !== null && $resultsDivisionId !== '' ? (int) $resultsDivisionId : null;
            $summaries = $this->reporting->getCompletedBracketSummaries($id, $divisionIdFilter);
            $teamsForResults = TournamentWrestler::where('Tournament_id', $id)->distinct()->pluck('wr_club')->filter()->sort()->values();
            foreach ($summaries as $s) {
                $placements = $this->reporting->getPlacementsForBracket($id, $s['bracket_id']);
                if ($resultsTeam !== null && $resultsTeam !== '') {
                    $placements = array_values(array_filter($placements, fn ($p) => (string) ($p['club'] ?? '') === (string) $resultsTeam));
                }
                $resultsBrackets[] = (object) [
                    'bracket_id' => $s['bracket_id'],
                    'group_name' => $s['group_name'] ?? 'Bracket ' . $s['bracket_id'],
                    'placements' => $placements,
                ];
            }
        }

        $bracketOptions = [];
        $selectedBracketsData = [];
        $selectedBracketIds = [];
        if ($tab === 'brackets') {
            $bracketOptions = $this->reporting->getCompletedBracketSummaries($id)->values()->all();
            $raw = $request->query('brackets');
            if (is_array($raw)) {
                $selectedBracketIds = array_values(array_map('intval', array_filter($raw)));
            } elseif (is_string($raw) && $raw !== '') {
                $selectedBracketIds = array_values(array_unique(array_map('intval', array_filter(explode(',', $raw)))));
            }
            foreach ($selectedBracketIds as $bid) {
                if (! $this->reporting->isBracketComplete($id, $bid)) {
                    continue;
                }
                $meta = $this->reporting->getBracketMeta($id, $bid);
                $bouts = $this->reporting->getBoutsForBracket($id, $bid);
                $selectedBracketsData[] = (object) [
                    'bracket_id' => $bid,
                    'meta' => $meta,
                    'bouts' => $bouts,
                ];
            }
        }

        return view('tournaments.show', [
            'tournament' => $tournament,
            'tab' => $tab,
            'registrationOpen' => $registrationOpen,
            'registrationLocked' => $registrationLocked,
            'userWrestlers' => $userWrestlers,
            'statusByWrestler' => $statusByWrestler,
            'teamsData' => $teamsData,
            'teamsDivisionId' => $teamsDivisionId,
            'resultsBrackets' => $resultsBrackets,
            'resultsDivisionId' => $resultsDivisionId,
            'resultsTeam' => $resultsTeam,
            'teamsForResults' => $teamsForResults,
            'bracketOptions' => $bracketOptions,
            'selectedBracketsData' => $selectedBracketsData,
            'selectedBracketIds' => $selectedBracketIds,
        ]);
    }

    /**
     * Search for a wrestler by last name to view their bouts (public).
     */
    public function myboutSearch(Request $request, int $tid): View
    {
        $tournament = Tournament::findOrFail($tid);
        $wrestlers = null;
        if ($request->filled('name')) {
            $lastname = $request->input('name');
            $wrestlers = TournamentWrestler::where('Tournament_id', $tid)
                ->where('wr_last_name', 'like', $lastname . '%')
                ->orderBy('wr_last_name')
                ->orderBy('wr_first_name')
                ->get();
        }

        return view('tournaments.boutsearch', [
            'tournament' => $tournament,
            'wrestlers' => $wrestlers,
            'tid' => $tid,
        ]);
    }

    /**
     * Show bouts for a specific wrestler (public).
     */
    public function mybouts(int $tid, int $wid): View
    {
        $tournament = Tournament::findOrFail($tid);
        $wrestler = TournamentWrestler::where('id', $wid)->where('Tournament_id', $tid)->firstOrFail();

        $boutRows = Bout::where('Wrestler_Id', $wid)
            ->where('Tournament_Id', $tid)
            ->orderBy('round')
            ->orderBy('id')
            ->get();

        $data = [];
        foreach ($boutRows as $row) {
            $opponentRow = Bout::where('id', $row->id)
                ->where('Tournament_Id', $tid)
                ->where('Wrestler_Id', '!=', $wid)
                ->first();
            $opponent = $opponentRow
                ? TournamentWrestler::where('id', $opponentRow->Wrestler_Id)->where('Tournament_id', $tid)->first()
                : null;
            $data[] = (object) [
                'bout_id' => $row->id,
                'bout_number' => $row->bout_number,
                'round' => $row->round,
                'opponent_name' => $opponent ? trim($opponent->wr_first_name . ' ' . $opponent->wr_last_name) : '–',
                'opponent_club' => $opponent ? $opponent->wr_club : '–',
                'score' => $row->score ?? '0',
                'pin' => (bool) $row->pin,
            ];
        }

        return view('tournaments.mybouts', [
            'tournament' => $tournament,
            'wrestler' => $wrestler,
            'data' => $data,
            'tid' => $tid,
        ]);
    }
}

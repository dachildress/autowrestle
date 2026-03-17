<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Services\BracketReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function __construct(
        private BracketReportingService $reporting
    ) {}

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
        abort(403, 'You cannot view reports for this tournament.');
    }

    /**
     * Reports hub for the selected tournament: links to Completed Brackets, Group Results, Bracket Results, Wrestler Results.
     */
    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);

        return view('manage.reports.index', compact('tournament'));
    }

    /**
     * Completed brackets list for this tournament. Optional filters: date. Export CSV, print-friendly.
     */
    public function completed(Request $request, int $tid): View|StreamedResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);

        $dateFrom = $request->query('date_from') ?: null;
        $dateTo = $request->query('date_to') ?: null;

        $summaries = $this->reporting->getCompletedBracketSummaries($tid, null, null, $dateFrom, $dateTo);

        if ($request->query('export') === 'csv') {
            return $this->exportCompletedCsv($summaries);
        }

        return view('manage.reports.completed', [
            'tournament' => $tournament,
            'summaries' => $summaries,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    private function exportCompletedCsv($summaries): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="completed-brackets-' . date('Y-m-d') . '.csv"',
        ];

        return Response::stream(function () use ($summaries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tournament', 'Tournament Date', 'Division', 'Group', 'Bracket ID', 'Wrestlers', 'Completed At', 'Champion']);
            foreach ($summaries as $s) {
                fputcsv($out, [
                    $s['tournament_name'],
                    $s['tournament_date'] ? $s['tournament_date']->format('Y-m-d') : '',
                    $s['division_name'],
                    $s['group_name'],
                    $s['bracket_id'],
                    $s['wrestler_count'],
                    $s['completed_at'] ? ($s['completed_at'] instanceof \DateTimeInterface ? $s['completed_at']->format('Y-m-d H:i') : $s['completed_at']) : '',
                    $s['champion'] ?? '',
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /**
     * Group results for this tournament: groups with completed brackets.
     */
    public function groups(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);

        $summaries = $this->reporting->getCompletedBracketSummaries($tid);
        $byGroup = $summaries->groupBy('group_id');
        $groups = DivGroup::where('Tournament_Id', $tid)->get()->keyBy('id');

        $groupSummaries = [];
        foreach ($byGroup as $gid => $items) {
            if ($gid === null || $gid === '') {
                continue;
            }
            $group = $groups->get($gid);
            $groupSummaries[] = [
                'group_id' => $gid,
                'group_name' => $group ? $group->Name : 'Group ' . $gid,
                'bracket_count' => $items->count(),
                'brackets' => $items->values()->all(),
            ];
        }

        return view('manage.reports.groups', [
            'tournament' => $tournament,
            'groupSummaries' => $groupSummaries,
        ]);
    }

    /**
     * Single group results: all completed brackets in one group.
     */
    public function groupShow(Request $request, int $tid, int $gid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $group = DivGroup::where('id', $gid)->where('Tournament_Id', $tid)->firstOrFail();

        $summaries = $this->reporting->getCompletedBracketsForGroup($tid, $gid);

        return view('manage.reports.group-show', [
            'tournament' => $tournament,
            'group' => $group,
            'summaries' => $summaries,
        ]);
    }

    /**
     * Bracket results for this tournament. Optional filter: bracket ID.
     */
    public function brackets(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);

        $bid = $request->query('bracket_id') ? (int) $request->query('bracket_id') : null;

        $summaries = $this->reporting->getCompletedBracketSummaries($tid);
        if ($bid !== null) {
            $summaries = $summaries->where('bracket_id', $bid)->values();
        }

        return view('manage.reports.brackets', [
            'tournament' => $tournament,
            'summaries' => $summaries,
            'filters' => ['bracket_id' => $bid],
        ]);
    }

    /**
     * Single bracket result detail: placements, print-friendly.
     */
    public function bracketShow(Request $request, int $tid, int $bid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);

        if (! $this->reporting->isBracketComplete($tid, $bid)) {
            abort(404, 'Bracket is not completed or does not exist.');
        }

        $meta = $this->reporting->getBracketMeta($tid, $bid);
        $placements = $this->reporting->getPlacementsForBracket($tid, $bid);
        $completedAt = $this->reporting->getBracketCompletedAt($tid, $bid);

        return view('manage.reports.bracket-show', [
            'tournament' => $tournament,
            'meta' => $meta,
            'placements' => $placements,
            'completed_at' => $completedAt,
            'bracket_id' => $bid,
        ]);
    }

    /**
     * Wrestler results for this tournament: search by name/team, show placement history.
     */
    public function wrestlers(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);

        $q = $request->query('q');
        $team = $request->query('team') ? trim($request->query('team')) : null;

        $results = [];
        if ($q !== null && $q !== '') {
            $query = TournamentWrestler::query()
                ->where('Tournament_id', $tid)
                ->where(function ($qb) use ($q) {
                    $qb->where('wr_first_name', 'like', '%' . $q . '%')
                        ->orWhere('wr_last_name', 'like', '%' . $q . '%')
                        ->orWhereRaw("CONCAT(wr_first_name, ' ', wr_last_name) LIKE ?", ['%' . $q . '%'])
                        ->orWhereRaw("CONCAT(wr_last_name, ' ', wr_first_name) LIKE ?", ['%' . $q . '%']);
                });
            if ($team !== null && $team !== '') {
                $query->where('wr_club', 'like', '%' . $team . '%');
            }
            $wrestlers = $query->with('tournament')->orderBy('wr_last_name')->orderBy('wr_first_name')->limit(100)->get();

            foreach ($wrestlers as $tw) {
                $history = $this->reporting->getWrestlerPlacementHistory($tw->id);
                $results[] = [
                    'wrestler' => $tw,
                    'history' => $history,
                ];
            }
        }

        return view('manage.reports.wrestlers', [
            'tournament' => $tournament,
            'results' => $results,
            'filters' => [
                'q' => $q,
                'team' => $team,
            ],
        ]);
    }
}

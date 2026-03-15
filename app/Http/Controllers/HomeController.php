<?php

namespace App\Http\Controllers;

use App\Models\Bout;
use App\Models\BoutScoringState;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Services\DivisionPeriodService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private DivisionPeriodService $periodService
    ) {}
    /**
     * Home page: tournaments, top 5 fastest pins, top 5 wrestlers by win ratio.
     */
    public function index(): View
    {
        $tournaments = Tournament::upcomingAndOpen()->orderBy('TournamentDate', 'asc')->get();

        $quickPins = $this->getTop5FastestPins();
        $topRankings = $this->getTop5ByWinRatio();

        return view('home', compact('tournaments', 'quickPins', 'topRankings'));
    }

    /**
     * Compute match time at pin from state period/clock when division is known. Returns "M:SS" or null.
     */
    private function computePinTimeFromState(BoutScoringState $state, array $boutDivisions): ?string
    {
        $boutKey = $state->bout_id . '-' . $state->tournament_id;
        $divisionId = $boutDivisions[$boutKey] ?? null;
        if ($divisionId === null) {
            return null;
        }
        $period = max(1, (int) $state->period);
        $clockRemaining = max(0, (int) $state->clock_seconds);
        $totalSeconds = 0;
        for ($p = 1; $p < $period; $p++) {
            $totalSeconds += $this->periodService->getPeriodDurationByNumber($divisionId, $p);
        }
        $currentPeriodDuration = $this->periodService->getPeriodDurationByNumber($divisionId, $period);
        $totalSeconds += $currentPeriodDuration - $clockRemaining;
        $totalSeconds = max(0, $totalSeconds);
        $minutes = (int) floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;
        return $minutes . ':' . str_pad((string) $seconds, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Parse wrtime string (e.g. "1:23", "0:15", "45") to seconds for sorting.
     */
    private function parseWrtimeToSeconds(?string $wrtime): int
    {
        if ($wrtime === null || $wrtime === '') {
            return 999999;
        }
        $wrtime = trim($wrtime);
        if (str_contains($wrtime, ':')) {
            $parts = explode(':', $wrtime, 2);
            $minutes = (int) ($parts[0] ?? 0);
            $seconds = (int) ($parts[1] ?? 0);
            return $minutes * 60 + $seconds;
        }
        return (int) (floatval($wrtime));
    }

    /**
     * Top 5 fastest pin times. Uses bout_scoring_state (mat scoring stores result_type = 'Pin')
     * and optionally bouts.wrtime when set (legacy or if synced). Pins without a time show "—".
     */
    private function getTop5FastestPins(): array
    {
        $states = BoutScoringState::query()
            ->where('status', 'completed')
            ->where('result_type', 'Pin')
            ->whereNotNull('winner_id')
            ->get();

        if ($states->isEmpty()) {
            return [];
        }

        $winnerIds = $states->pluck('winner_id')->unique()->all();
        $wrestlers = TournamentWrestler::whereIn('id', $winnerIds)
            ->get()
            ->keyBy('id');

        $boutIds = $states->pluck('bout_id')->unique()->all();
        $tournamentIds = $states->pluck('tournament_id')->unique()->all();
        $boutWrtimes = [];
        $boutDivisions = [];
        if (! empty($boutIds) && ! empty($tournamentIds)) {
            $rows = Bout::query()
                ->whereIn('id', $boutIds)
                ->whereIn('Tournament_Id', $tournamentIds)
                ->select('id', 'Tournament_Id', 'Wrestler_Id', 'wrtime', 'Division_Id')
                ->get();
            foreach ($rows as $r) {
                $key = $r->id . '-' . $r->Tournament_Id . '-' . $r->Wrestler_Id;
                if ($r->wrtime !== null && $r->wrtime !== '') {
                    $boutWrtimes[$key] = $r->wrtime;
                }
                $boutKey = $r->id . '-' . $r->Tournament_Id;
                if (! isset($boutDivisions[$boutKey]) && $r->Division_Id) {
                    $boutDivisions[$boutKey] = (int) $r->Division_Id;
                }
            }
        }

        $pins = $states->map(function ($state) use ($wrestlers, $boutWrtimes, $boutDivisions) {
            $tw = $wrestlers->get($state->winner_id);
            $key = $state->bout_id . '-' . $state->tournament_id . '-' . $state->winner_id;
            $wrtime = $boutWrtimes[$key] ?? null;
            if ($wrtime === null) {
                $wrtime = $this->computePinTimeFromState($state, $boutDivisions);
            }
            return [
                'name' => $tw ? trim($tw->wr_first_name . ' ' . $tw->wr_last_name) : 'Unknown',
                'club' => $tw ? ($tw->wr_club ?? '—') : '—',
                'wrtime' => $wrtime ?? '—',
                'seconds' => $this->parseWrtimeToSeconds($wrtime),
            ];
        })->sortBy('seconds')->values()->take(5)->all();

        return array_values($pins);
    }

    /**
     * Top 5 wrestlers by win ratio from completed bouts (bout_scoring_state).
     * wr_wins/wr_losses on tournamentwrestlers are not updated by mat scoring, so we compute from results.
     */
    private function getTop5ByWinRatio(): array
    {
        $states = BoutScoringState::query()
            ->where('status', 'completed')
            ->select('red_wrestler_id', 'green_wrestler_id', 'winner_id')
            ->get();

        $wins = [];
        $losses = [];
        $participantIds = [];

        foreach ($states as $s) {
            $red = $s->red_wrestler_id;
            $green = $s->green_wrestler_id;
            $winner = $s->winner_id;
            foreach ([$red, $green] as $id) {
                if ($id === null) {
                    continue;
                }
                $participantIds[$id] = true;
                if ($winner === null) {
                    $losses[$id] = ($losses[$id] ?? 0) + 1;
                } elseif ($winner === $id) {
                    $wins[$id] = ($wins[$id] ?? 0) + 1;
                } else {
                    $losses[$id] = ($losses[$id] ?? 0) + 1;
                }
            }
        }

        $ids = array_keys($participantIds);
        if (empty($ids)) {
            return [];
        }

        $wrestlers = TournamentWrestler::whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $ranked = collect($ids)->map(function ($id) use ($wrestlers, $wins, $losses) {
            $w = (int) ($wins[$id] ?? 0);
            $l = (int) ($losses[$id] ?? 0);
            $total = $w + $l;
            $ratio = $total > 0 ? $w / $total : 0;
            $tw = $wrestlers->get($id);
            return [
                'name' => $tw ? trim($tw->wr_first_name . ' ' . $tw->wr_last_name) : '—',
                'club' => $tw ? ($tw->wr_club ?? '—') : '—',
                'wins' => $w,
                'losses' => $l,
                'ratio' => $ratio,
            ];
        })->filter(fn ($r) => $r['wins'] + $r['losses'] >= 1)
            ->sortByDesc('ratio')
            ->values()
            ->take(5)
            ->all();

        return array_values($ranked);
    }
}

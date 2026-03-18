<?php

namespace App\Http\Controllers;

use App\Models\Bout;
use App\Models\BoutScoringEvent;
use App\Models\BoutScoringState;
use App\Models\Division;
use App\Models\TournamentWrestler;
use App\Services\DivisionPeriodService;
use App\Services\MatScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Mat-side bout scoring screen. Loads state, wrestlers, events; Phase 5 wires clock/buttons via endpoints.
 */
class MatBoutController extends Controller
{
    public function __construct(
        private MatScoringService $scoringService,
        private DivisionPeriodService $periodService
    ) {}

    /**
     * Show mat-side scoring screen for a bout. Scorer must be assigned to this bout's mat and tournament.
     */
    public function show(Request $request, int $boutId): View
    {
        $user = $request->user();
        $this->authorizeScorer($user);

        $tid = (int) $user->Tournament_id;
        $rows = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $user->mat_number)
            ->orderBy('Wrestler_Id')
            ->get();

        if ($rows->count() < 2) {
            abort(404, 'Bout not found or not on your mat.');
        }

        $redWrestler = TournamentWrestler::find($rows[0]->Wrestler_Id);
        $greenWrestler = TournamentWrestler::find($rows[1]->Wrestler_Id);
        if (!$redWrestler || !$greenWrestler) {
            abort(404, 'Bout wrestlers not found.');
        }

        $divisionId = $rows[0]->Division_Id ? (int) $rows[0]->Division_Id : null;
        $divisionName = '–';
        if ($divisionId) {
            $div = Division::find($divisionId);
            if ($div) {
                $divisionName = $div->DivisionName;
            }
        }
        $initialClockSeconds = $divisionId
            ? $this->periodService->getPeriodDuration($divisionId, '1')
            : DivisionPeriodService::DEFAULT_DURATIONS['1'];

        $state = $this->scoringService->getOrCreateState(
            $tid,
            $boutId,
            $redWrestler->id,
            $greenWrestler->id,
            $initialClockSeconds
        );
        $state->load(['redWrestler', 'greenWrestler']);

        $request->session()->put('mat_current_bout_id', $boutId);

        $events = BoutScoringEvent::where('tournament_id', $tid)
            ->where('bout_id', $boutId)
            ->orderBy('id')
            ->limit(100)
            ->get();

        $periodLabels = $this->periodService->getOrderedPeriodCodes();
        $periodDurations = [];
        if ($divisionId) {
            foreach ($periodLabels as $code) {
                $periodDurations[$code] = $this->periodService->getPeriodDuration($divisionId, $code);
            }
        } else {
            $periodDurations = DivisionPeriodService::DEFAULT_DURATIONS;
        }

        $boutNumber = $rows[0]->bout_number ?? $boutId;

        return view('mat.bout-show', [
            'boutId' => $boutId,
            'boutNumber' => $boutNumber,
            'matNumber' => $user->mat_number,
            'state' => $state,
            'redWrestler' => $redWrestler,
            'greenWrestler' => $greenWrestler,
            'divisionName' => $divisionName,
            'divisionId' => $divisionId,
            'initialClockSeconds' => $initialClockSeconds,
            'periodDurations' => $periodDurations,
            'events' => $events,
            'showHeadNeck' => $request->session()->get('mat_display_show_head_neck', false),
            'showRecover' => $request->session()->get('mat_display_show_recover', false),
        ]);
    }

    /**
     * Summary / history for a bout: events grouped by period.
     */
    public function history(Request $request, int $boutId): View
    {
        $user = $request->user();
        $this->authorizeScorer($user);

        $tid = (int) $user->Tournament_id;
        $rows = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $user->mat_number)
            ->orderBy('Wrestler_Id')
            ->get();

        if ($rows->count() < 2) {
            abort(404, 'Bout not found or not on your mat.');
        }

        $redWrestler = TournamentWrestler::find($rows[0]->Wrestler_Id);
        $greenWrestler = TournamentWrestler::find($rows[1]->Wrestler_Id);
        if (!$redWrestler || !$greenWrestler) {
            abort(404, 'Bout wrestlers not found.');
        }

        $divisionName = '–';
        if ($rows[0]->Division_Id) {
            $div = Division::find($rows[0]->Division_Id);
            if ($div) {
                $divisionName = $div->DivisionName;
            }
        }

        $state = BoutScoringState::where('tournament_id', $tid)->where('bout_id', $boutId)->first();
        $events = BoutScoringEvent::where('tournament_id', $tid)
            ->where('bout_id', $boutId)
            ->orderBy('id')
            ->get();

        $eventsByPeriod = $events->groupBy(fn ($e) => $e->period ?? 0);
        $boutNumber = $rows[0]->bout_number ?? $boutId;

        return view('mat.bout-history', [
            'boutId' => $boutId,
            'boutNumber' => $boutNumber,
            'matNumber' => $user->mat_number,
            'state' => $state,
            'redWrestler' => $redWrestler,
            'greenWrestler' => $greenWrestler,
            'divisionName' => $divisionName,
            'eventsByPeriod' => $eventsByPeriod,
        ]);
    }

    /**
     * Show Results form: select winner (auto-selected by score), result type, match end time.
     */
    public function results(Request $request, int $boutId): View|RedirectResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);

        $tid = (int) $user->Tournament_id;
        $rows = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $user->mat_number)
            ->orderBy('Wrestler_Id')
            ->get();

        if ($rows->count() < 2) {
            abort(404, 'Bout not found or not on your mat.');
        }

        $redWrestler = TournamentWrestler::find($rows[0]->Wrestler_Id);
        $greenWrestler = TournamentWrestler::find($rows[1]->Wrestler_Id);
        if (!$redWrestler || !$greenWrestler) {
            abort(404, 'Bout wrestlers not found.');
        }

        $divisionName = '–';
        if ($rows[0]->Division_Id) {
            $div = Division::find($rows[0]->Division_Id);
            if ($div) {
                $divisionName = $div->DivisionName;
            }
        }

        $divisionId = $rows[0]->Division_Id ? (int) $rows[0]->Division_Id : null;
        $initialClock = $divisionId
            ? $this->periodService->getPeriodDuration($divisionId, '1')
            : DivisionPeriodService::DEFAULT_DURATIONS['1'];
        $state = $this->scoringService->getOrCreateState(
            $tid,
            $boutId,
            $redWrestler->id,
            $greenWrestler->id,
            $initialClock
        );
        $state->load(['redWrestler', 'greenWrestler']);

        $defaultWinnerId = null;
        if ($state->isCompleted() && $state->winner_id) {
            $defaultWinnerId = $state->winner_id;
        } elseif ($state->red_score > $state->green_score) {
            $defaultWinnerId = $redWrestler->id;
        } elseif ($state->green_score > $state->red_score) {
            $defaultWinnerId = $greenWrestler->id;
        }

        $defaultResultType = $this->defaultResultTypeForState($state);
        $resultTypes = [
            'Decision' => 'Decision',
            'Pin' => 'Pin',
            'Technical Fall' => 'Technical Fall',
            'Forfeit' => 'Forfeit',
            'Default' => 'Default',
            'Disqualification' => 'Disqualification',
            'Double Forfeit' => 'Double Forfeit',
            'Tiebreak' => 'Tiebreak',
        ];

        $completedAt = $state->completed_at ?? now();
        $boutNumber = $rows[0]->bout_number ?? $boutId;
        return view('mat.bout-results', [
            'boutId' => $boutId,
            'boutNumber' => $boutNumber,
            'matNumber' => $user->mat_number,
            'state' => $state,
            'redWrestler' => $redWrestler,
            'greenWrestler' => $greenWrestler,
            'divisionName' => $divisionName,
            'defaultWinnerId' => $defaultWinnerId,
            'defaultResultType' => $defaultResultType,
            'resultTypes' => $resultTypes,
            'defaultMonth' => (int) $completedAt->format('n'),
            'defaultDay' => (int) $completedAt->format('j'),
            'defaultYear' => (int) $completedAt->format('Y'),
            'defaultHour' => (int) $completedAt->format('g'),
            'defaultMinute' => (int) $completedAt->format('i'),
            'defaultAmPm' => $completedAt->format('a'),
        ]);
    }

    /**
     * Save result: winner, result type, match end time. Validates double-forfeit when both wrestlers unknown.
     */
    public function saveResult(Request $request, int $boutId): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);

        $tid = (int) $user->Tournament_id;
        $rows = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $user->mat_number)
            ->orderBy('Wrestler_Id')
            ->get();

        if ($rows->count() < 2) {
            return redirect()->route('mat.dashboard')->with('error', 'Bout not found.');
        }

        $redWrestler = TournamentWrestler::find($rows[0]->Wrestler_Id);
        $greenWrestler = TournamentWrestler::find($rows[1]->Wrestler_Id);
        if (!$redWrestler || !$greenWrestler) {
            return redirect()->route('mat.dashboard')->with('error', 'Bout wrestlers not found.');
        }

        $state = BoutScoringState::where('tournament_id', $tid)->where('bout_id', $boutId)->first();
        if (!$state) {
            return redirect()->route('mat.bout.show', ['boutId' => $boutId])->with('error', 'No scoring state.');
        }
        if ($state->isCompleted()) {
            return redirect()->route('mat.bout.results', ['boutId' => $boutId])->with('info', 'This bout is already completed.');
        }

        $allowedResultTypes = ['Decision', 'Pin', 'Technical Fall', 'Forfeit', 'Default', 'Disqualification', 'Double Forfeit', 'Tiebreak'];
        $request->validate([
            'winner_id' => 'nullable|integer|in:' . $redWrestler->id . ',' . $greenWrestler->id,
            'result_type' => 'required|string|max:30|in:' . implode(',', $allowedResultTypes),
            'month' => 'required|integer|min:1|max:12',
            'day' => 'required|integer|min:1|max:31',
            'year' => 'required|integer|min:2000|max:2100',
            'hour' => 'required|integer|min:1|max:12',
            'minute' => 'required|integer|min:0|max:59',
            'am_pm' => 'required|string|in:am,pm',
        ]);

        $winnerId = $request->input('winner_id') ? (int) $request->input('winner_id') : null;
        $resultType = $request->input('result_type');

        $redUnknown = $this->isWrestlerUnknown($redWrestler);
        $greenUnknown = $this->isWrestlerUnknown($greenWrestler);
        if ($redUnknown && $greenUnknown && $resultType !== 'Double Forfeit') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['result_type' => 'The wrestlers cannot both be "Unknown" if you want to save the result as anything other than a double forfeit.']);
        }

        $hour = (int) $request->input('hour');
        if ($request->input('am_pm') === 'pm' && $hour !== 12) {
            $hour += 12;
        } elseif ($request->input('am_pm') === 'am' && $hour === 12) {
            $hour = 0;
        }
        $completedAt = \Carbon\Carbon::create(
            (int) $request->input('year'),
            (int) $request->input('month'),
            (int) $request->input('day'),
            $hour,
            (int) $request->input('minute'),
            0
        );

        $this->scoringService->completeBout($state, $winnerId, $resultType, $completedAt);

        return redirect()->route('mat.dashboard')
            ->with('success', 'Result saved.');
    }

    /**
     * Default result type for scorer convenience: Pin if match ended before 3 periods,
     * Technical Fall if leading score >= 15, otherwise Decision.
     */
    private function defaultResultTypeForState(BoutScoringState $state): string
    {
        if ($state->isCompleted() && $state->result_type) {
            return $state->result_type;
        }
        if ($state->period < 3) {
            return 'Pin';
        }
        $leadingScore = max($state->red_score, $state->green_score);
        return $leadingScore >= 15 ? 'Technical Fall' : 'Decision';
    }

    private function isWrestlerUnknown(TournamentWrestler $w): bool
    {
        $first = trim((string) ($w->wr_first_name ?? ''));
        $last = trim((string) ($w->wr_last_name ?? ''));
        $club = trim((string) ($w->wr_club ?? ''));
        if ($first === '' && $last === '') {
            return true;
        }
        $u = strtolower($first . ' ' . $last . ' ' . $club);
        return str_contains($u, 'unknown') || str_contains($u, 'unattached');
    }

    /**
     * Reset scoring state (scores, clock, period, timers). Does not delete events.
     */
    public function reset(Request $request, int $boutId): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);

        $tid = (int) $user->Tournament_id;
        $onMyMat = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $user->mat_number)
            ->exists();
        if (!$onMyMat) {
            return redirect()->route('mat.dashboard')->with('error', 'Bout not on your mat.');
        }

        $boutRow = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $user->mat_number)
            ->first();
        $divisionId = $boutRow && $boutRow->Division_Id ? (int) $boutRow->Division_Id : null;

        $state = \App\Models\BoutScoringState::where('tournament_id', $tid)
            ->where('bout_id', $boutId)
            ->first();

        if (!$state) {
            return redirect()->route('mat.bout.show', ['boutId' => $boutId])
                ->with('error', 'No scoring state found for this bout.');
        }

        $this->scoringService->resetBout($state, $divisionId);
        return redirect()->route('mat.bout.show', ['boutId' => $boutId])
            ->with('success', 'Bout reset.');
    }

    /**
     * GET state JSON for JS (clock, scores, period, timers, events).
     */
    public function state(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        return response()->json($this->stateToArray($state));
    }

    /**
     * POST clock: action = start|stop|set. For set, pass clock_seconds.
     */
    public function clock(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        if ($state->isCompleted()) {
            return response()->json(['error' => 'Bout completed'], 422);
        }
        $action = $request->input('action');
        if ($action === 'start') {
            $this->scoringService->setStatus($state, 'live');
        } elseif ($action === 'stop') {
            $this->scoringService->setStatus($state, 'paused');
        } elseif ($action === 'set') {
            $sec = (int) $request->input('clock_seconds', 0);
            $this->scoringService->setClock($state, max(0, $sec));
        } else {
            return response()->json(['error' => 'Invalid action'], 422);
        }
        $state->refresh();
        return response()->json($this->stateToArray($state));
    }

    /**
     * POST set period or advance to next. action=next: use division timing rules; period=N: set period and clock.
     */
    public function period(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        if ($state->isCompleted()) {
            return response()->json(['error' => 'Bout completed'], 422);
        }

        $divisionId = $this->getDivisionIdForBout($request, $boutId);

        if ($request->input('action') === 'next') {
            $next = $this->periodService->getNextPeriod(
                $state->period,
                $state->red_score,
                $state->green_score
            );
            if ($next === null) {
                if ($this->periodService->isTiedAfterOT3($state->period, $state->red_score, $state->green_score)) {
                    $this->scoringService->completeBout($state, null, 'Tiebreak');
                } else {
                    $winnerId = $state->red_score > $state->green_score
                        ? $state->red_wrestler_id
                        : $state->green_wrestler_id;
                    $this->scoringService->completeBout($state, $winnerId, null);
                }
            } else {
                $clockSeconds = $divisionId
                    ? $this->periodService->getPeriodDurationByNumber($divisionId, $next)
                    : (DivisionPeriodService::DEFAULT_DURATIONS[$this->periodService->periodNumberToCode($next)] ?? 60);
                $this->scoringService->setPeriod($state, $next, $clockSeconds);
            }
        } else {
            $period = (int) $request->input('period', 1);
            $period = max(1, min(6, $period));
            $clockSeconds = $divisionId
                ? $this->periodService->getPeriodDurationByNumber($divisionId, $period)
                : (DivisionPeriodService::DEFAULT_DURATIONS[$this->periodService->periodNumberToCode($period)] ?? 60);
            $this->scoringService->setPeriod($state, $period, $clockSeconds);
        }

        $state->refresh();
        return response()->json($this->stateToArray($state));
    }

    /**
     * POST record scoring event (side, event_type, points?, period?, match_time_snapshot?). Updates score if points.
     */
    public function event(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        if ($state->isCompleted()) {
            return response()->json(['error' => 'Bout completed'], 422);
        }
        $request->validate([
            'side' => 'required|string|in:red,green,neutral',
            'event_type' => 'required|string|max:50',
            'points' => 'nullable|integer',
            'period' => 'nullable|integer|min:1',
            'match_time_snapshot' => 'nullable|integer|min:0',
        ]);
        $tid = (int) $user->Tournament_id;
        $points = (int) $request->input('points', 0);
        $this->scoringService->recordEvent(
            $tid,
            $boutId,
            $request->input('side'),
            $request->input('event_type'),
            $points,
            $request->input('period') ? (int) $request->input('period') : null,
            $request->input('match_time_snapshot') ? (int) $request->input('match_time_snapshot') : null,
            null,
            $user->id
        );
        if ($points !== 0 && in_array($request->input('side'), ['red', 'green'], true)) {
            $redScore = $state->red_score + ($request->input('side') === 'red' ? $points : 0);
            $greenScore = $state->green_score + ($request->input('side') === 'green' ? $points : 0);
            $this->scoringService->updateScores($state, max(0, $redScore), max(0, $greenScore));
        }
        $state->refresh();
        return response()->json($this->stateToArray($state));
    }

    /**
     * POST add comment (note). Records neutral event.
     */
    public function comment(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        if ($state->isCompleted()) {
            return response()->json(['error' => 'Bout completed'], 422);
        }
        $note = $request->input('note', '');
        $tid = (int) $user->Tournament_id;
        $this->scoringService->recordEvent($tid, $boutId, 'neutral', 'comment', 0, null, null, $note, $user->id);
        $state->refresh();
        return response()->json($this->stateToArray($state));
    }

    /**
     * POST set side timer (timer = blood_time_red, injury_time_green, etc.), seconds.
     */
    public function timer(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        if ($state->isCompleted()) {
            return response()->json(['error' => 'Bout completed'], 422);
        }
        $timer = $request->input('timer');
        $allowed = [
            'blood_time_red', 'blood_time_green', 'injury_time_red', 'injury_time_green',
            'head_neck_time_red', 'head_neck_time_green', 'recovery_time_red', 'recovery_time_green',
        ];
        if (!in_array($timer, $allowed, true)) {
            return response()->json(['error' => 'Invalid timer'], 422);
        }
        $seconds = max(0, (int) $request->input('seconds', 0));
        $this->scoringService->setSideTimer($state, $timer, $seconds);
        $state->refresh();
        return response()->json($this->stateToArray($state));
    }

    /**
     * POST set display_swap (true = left panel shows green on scorer and virtual display).
     */
    public function displaySwap(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        $displaySwap = $request->boolean('display_swap');
        $state->update(['display_swap' => $displaySwap]);
        $state->refresh();
        return response()->json($this->stateToArray($state));
    }

    /**
     * POST complete bout (winner_id?, result_type?).
     */
    public function complete(Request $request, int $boutId): JsonResponse
    {
        $user = $request->user();
        $this->authorizeScorer($user);
        $state = $this->getStateForBout($request, $boutId);
        $winnerId = $request->input('winner_id') ? (int) $request->input('winner_id') : null;
        $resultType = $request->input('result_type');
        $this->scoringService->completeBout($state, $winnerId, $resultType);
        $state->refresh();
        return response()->json($this->stateToArray($state));
    }

    private function getStateForBout(Request $request, int $boutId): BoutScoringState
    {
        $tid = (int) $request->user()->Tournament_id;
        $onMyMat = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $request->user()->mat_number)
            ->exists();
        if (!$onMyMat) {
            abort(404, 'Bout not on your mat.');
        }
        $state = BoutScoringState::where('tournament_id', $tid)->where('bout_id', $boutId)->first();
        if (!$state) {
            abort(404, 'No scoring state.');
        }
        return $state;
    }

    private function getDivisionIdForBout(Request $request, int $boutId): ?int
    {
        $tid = (int) $request->user()->Tournament_id;
        $row = Bout::where('id', $boutId)
            ->where('Tournament_Id', $tid)
            ->where('mat_number', $request->user()->mat_number)
            ->first();
        return $row && $row->Division_Id ? (int) $row->Division_Id : null;
    }

    private function stateToArray(BoutScoringState $state): array
    {
        $state->load(['redWrestler', 'greenWrestler']);
        $events = BoutScoringEvent::where('tournament_id', $state->tournament_id)
            ->where('bout_id', $state->bout_id)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'side' => $e->side,
                'event_type' => $e->event_type,
                'points' => $e->points,
                'note' => $e->note,
            ]);
        return [
            'status' => $state->status,
            'display_swap' => (bool) $state->display_swap,
            'red_score' => $state->red_score,
            'green_score' => $state->green_score,
            'period' => $state->period,
            'clock_seconds' => $state->clock_seconds,
            'blood_time_red' => $state->blood_time_red,
            'blood_time_green' => $state->blood_time_green,
            'injury_time_red' => $state->injury_time_red,
            'injury_time_green' => $state->injury_time_green,
            'head_neck_time_red' => $state->head_neck_time_red,
            'head_neck_time_green' => $state->head_neck_time_green,
            'recovery_time_red' => $state->recovery_time_red,
            'recovery_time_green' => $state->recovery_time_green,
            'events' => $events,
        ];
    }

    private function authorizeScorer($user): void
    {
        if (!$user->isScorer()) {
            abort(403, 'Only scorer users can access mat-side scoring.');
        }
        if ($user->mat_number === null) {
            abort(403, 'You have no mat assigned.');
        }
        if (!$user->Tournament_id) {
            abort(403, 'No tournament assigned.');
        }
    }
}

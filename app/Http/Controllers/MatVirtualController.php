<?php

namespace App\Http\Controllers;

use App\Models\Bout;
use App\Models\BoutScoringState;
use App\Models\TournamentWrestler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Virtual audience display: settings (layout, font size) and fullscreen display (timer, scores, names).
 */
class MatVirtualController extends Controller
{
    /**
     * Settings page: layout dropdown, font size, Display button. Display always shows current match (session or first on mat).
     */
    public function settings(Request $request): View
    {
        $user = $request->user();
        if (!$user->isScorer()) {
            abort(403, 'Only scorer users can access the virtual display.');
        }
        if ($user->mat_number === null) {
            return view('mat.virtual.settings', ['matNumber' => null, 'layouts' => $this->layouts()]);
        }

        return view('mat.virtual.settings', [
            'matNumber' => $user->mat_number,
            'layouts' => $this->layouts(),
        ]);
    }

    /**
     * Audience display: always shows current match (session bout or first incomplete on mat). Polls state so points and timer update live.
     */
    public function display(Request $request): View
    {
        $user = $request->user();
        if (!$user->isScorer()) {
            abort(403, 'Only scorer users can access the virtual display.');
        }

        $tid = (int) $user->Tournament_id;
        $boutId = $request->query('bout_id') ? (int) $request->query('bout_id') : null;

        if (!$boutId) {
            $boutId = $request->session()->get('mat_current_bout_id');
        }
        $boutId = $this->resolveCurrentBoutId($tid, $user->mat_number, $boutId);
        if ($boutId) {
            $request->session()->put('mat_current_bout_id', $boutId);
        }

        $layout = $request->query('layout', 'Folkstyle');
        $fontPx = max(24, min(200, (int) $request->query('font', 84)));
        // Head/Neck and Recovery: only show when user checked them on Settings and saved (default false = hidden)
        $showHeadNeck = $request->query('show_head_neck') !== null
            ? $request->query('show_head_neck', '0') === '1'
            : $request->session()->get('mat_display_show_head_neck', false);
        $showRecover = $request->query('show_recover') !== null
            ? $request->query('show_recover', '0') === '1'
            : $request->session()->get('mat_display_show_recover', false);

        $stateUrl = route('mat.virtual.current-state');
        $initial = null;
        if ($boutId) {
            $onMat = Bout::where('id', $boutId)
                ->where('Tournament_Id', $tid)
                ->where('mat_number', $user->mat_number)
                ->exists();
            if ($onMat) {
                $state = BoutScoringState::where('tournament_id', $tid)->where('bout_id', $boutId)->first();
                if ($state) {
                    $state->load(['redWrestler', 'greenWrestler']);
                    $initial = [
                        'red_score' => $state->red_score,
                        'green_score' => $state->green_score,
                        'period' => $state->period,
                        'clock_seconds' => $state->clock_seconds,
                        'display_swap' => (bool) $state->display_swap,
                        'red_name' => $state->redWrestler ? trim($state->redWrestler->wr_first_name . ' ' . $state->redWrestler->wr_last_name) : 'Unknown',
                        'red_team' => $state->redWrestler ? ($state->redWrestler->wr_club ?? 'Unattached') : 'Unattached',
                        'green_name' => $state->greenWrestler ? trim($state->greenWrestler->wr_first_name . ' ' . $state->greenWrestler->wr_last_name) : 'Unknown',
                        'green_team' => $state->greenWrestler ? ($state->greenWrestler->wr_club ?? 'Unattached') : 'Unattached',
                        'head_neck_time_red' => $state->head_neck_time_red ?? 0,
                        'head_neck_time_green' => $state->head_neck_time_green ?? 0,
                        'recovery_time_red' => $state->recovery_time_red ?? 0,
                        'recovery_time_green' => $state->recovery_time_green ?? 0,
                    ];
                }
            }
        }

        if ($initial === null) {
            $initial = [
                'red_score' => 0,
                'green_score' => 0,
                'period' => 1,
                'clock_seconds' => 120,
                'display_swap' => false,
                'red_name' => 'Unknown',
                'red_team' => 'Unattached',
                'green_name' => 'Unknown',
                'green_team' => 'Unattached',
                'head_neck_time_red' => 0,
                'head_neck_time_green' => 0,
                'recovery_time_red' => 0,
                'recovery_time_green' => 0,
            ];
        }

        $periodLabels = [1 => 'Period 1', 2 => 'Period 2', 3 => 'Period 3', 4 => 'OT1', 5 => 'OT2', 6 => 'OT3'];

        return response()
            ->view('mat.virtual.display', [
                'boutId' => $boutId,
                'layout' => $layout,
                'fontPx' => $fontPx,
                'stateUrl' => $stateUrl,
                'initial' => $initial,
                'periodLabel' => $periodLabels[$initial['period']] ?? 'Period ' . $initial['period'],
                'showHeadNeck' => $showHeadNeck,
                'showRecover' => $showRecover,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * JSON: current match state (scores, timer, period, names). Display polls this so it always shows the current bout and live updates.
     */
    public function currentState(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isScorer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $tid = (int) $user->Tournament_id;
        $boutId = $request->session()->get('mat_current_bout_id');
        $boutId = $this->resolveCurrentBoutId($tid, $user->mat_number, $boutId);
        if (!$boutId) {
            return response()->json([
                'bout_id' => null,
                'red_score' => 0,
                'green_score' => 0,
                'period' => 1,
                'clock_seconds' => 0,
                'display_swap' => false,
                'red_name' => 'Unknown',
                'red_team' => 'Unattached',
                'green_name' => 'Unknown',
                'green_team' => 'Unattached',
                'head_neck_time_red' => 0,
                'head_neck_time_green' => 0,
                'recovery_time_red' => 0,
                'recovery_time_green' => 0,
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }
        $state = BoutScoringState::where('tournament_id', $tid)->where('bout_id', $boutId)->first();
        if (!$state) {
            return response()->json([
                'bout_id' => $boutId,
                'red_score' => 0,
                'green_score' => 0,
                'period' => 1,
                'clock_seconds' => 0,
                'display_swap' => false,
                'red_name' => 'Unknown',
                'red_team' => 'Unattached',
                'green_name' => 'Unknown',
                'green_team' => 'Unattached',
                'head_neck_time_red' => 0,
                'head_neck_time_green' => 0,
                'recovery_time_red' => 0,
                'recovery_time_green' => 0,
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }
        $state->load(['redWrestler', 'greenWrestler']);
        return response()->json([
            'bout_id' => $boutId,
            'red_score' => $state->red_score,
            'green_score' => $state->green_score,
            'period' => $state->period,
            'clock_seconds' => $state->clock_seconds,
            'display_swap' => (bool) $state->display_swap,
            'red_name' => $state->redWrestler ? trim($state->redWrestler->wr_first_name . ' ' . $state->redWrestler->wr_last_name) : 'Unknown',
            'red_team' => $state->redWrestler ? ($state->redWrestler->wr_club ?? 'Unattached') : 'Unattached',
            'green_name' => $state->greenWrestler ? trim($state->greenWrestler->wr_first_name . ' ' . $state->greenWrestler->wr_last_name) : 'Unknown',
            'green_team' => $state->greenWrestler ? ($state->greenWrestler->wr_club ?? 'Unattached') : 'Unattached',
            'head_neck_time_red' => $state->head_neck_time_red ?? 0,
            'head_neck_time_green' => $state->head_neck_time_green ?? 0,
            'recovery_time_red' => $state->recovery_time_red ?? 0,
            'recovery_time_green' => $state->recovery_time_green ?? 0,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Resolve current bout: prefer given id if still on mat and not completed, else first incomplete on mat.
     */
    private function resolveCurrentBoutId(int $tid, ?int $matNumber, ?int $preferBoutId): ?int
    {
        if ($matNumber === null) {
            return null;
        }
        $completedBoutIds = BoutScoringState::where('tournament_id', $tid)
            ->where('status', 'completed')
            ->pluck('bout_id')
            ->all();

        if ($preferBoutId) {
            $onMat = Bout::where('id', $preferBoutId)
                ->where('Tournament_Id', $tid)
                ->where('mat_number', $matNumber)
                ->exists();
            $notCompleted = empty($completedBoutIds) || !in_array($preferBoutId, $completedBoutIds, true);
            if ($onMat && $notCompleted) {
                return $preferBoutId;
            }
        }

        $query = Bout::where('Tournament_Id', $tid)
            ->where('mat_number', $matNumber)
            ->orderBy('round')
            ->orderBy('id');
        if (!empty($completedBoutIds)) {
            $query->whereNotIn('id', $completedBoutIds);
        }
        $first = $query->first();
        return $first ? (int) $first->id : null;
    }

    private function layouts(): array
    {
        return [
            'Folkstyle' => 'Folkstyle',
        ];
    }
}

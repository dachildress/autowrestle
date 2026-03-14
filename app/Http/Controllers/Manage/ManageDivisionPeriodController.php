<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\DivisionPeriodSetting;
use App\Models\Tournament;
use App\Services\DivisionPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManageDivisionPeriodController extends Controller
{
    public function __construct(
        private DivisionPeriodService $periodService
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
        abort(403, 'You cannot manage this tournament.');
    }

    /**
     * Show period settings for a division. Load existing or display defaults.
     */
    public function index(Request $request, int $tid, int $did): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $periods = $this->periodService->getOrderedPeriods($did);
        return view('manage.divisions.period-settings', [
            'tournament' => $tournament,
            'division' => $division,
            'periods' => $periods,
        ]);
    }

    /**
     * Save period durations. Expects minutes_X and seconds_X per period code; converts to duration_seconds.
     */
    public function store(Request $request, int $tid, int $did): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();

        $codes = DivisionPeriodService::PERIOD_CODES;
        $rules = [];
        foreach ($codes as $code) {
            $rules['minutes_' . $code] = 'required|integer|min:0|max:120';
            $rules['seconds_' . $code] = 'required|integer|min:0|max:59';
        }
        $valid = $request->validate($rules);

        $sortOrder = 1;
        foreach ($codes as $code) {
            $seconds = (int) $valid['minutes_' . $code] * 60 + (int) $valid['seconds_' . $code];
            $label = in_array($code, ['1', '2', '3'], true) ? 'Period ' . $code : $code;
            DivisionPeriodSetting::updateOrCreate(
                ['division_id' => $did, 'period_code' => $code],
                [
                    'period_label' => $label,
                    'sort_order' => $sortOrder,
                    'duration_seconds' => $seconds,
                ]
            );
            $sortOrder++;
        }

        return redirect()->route('manage.divisions.period-settings.index', [$tid, $did])
            ->with('success', 'Period settings saved.');
    }

    /**
     * Initialize default period rows for the division.
     */
    public function defaults(Request $request, int $tid, int $did): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $created = $this->periodService->initializeDefaultsForDivision($did);
        return redirect()->route('manage.divisions.period-settings.index', [$tid, $did])
            ->with('success', $created > 0 ? "Initialized {$created} default period(s)." : 'Division already has all period settings.');
    }
}

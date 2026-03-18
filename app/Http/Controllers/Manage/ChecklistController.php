<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\BoutNumberScheme;
use App\Models\Division;
use App\Models\Tournament;
use App\Models\TournamentChecklist;
use App\Models\TournamentWrestler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecklistController extends Controller
{
    /** Step key => [title, route name]. Route receives tid from URL. */
    private const STEP_CONFIG = [
        'update_tournament_info' => ['Update Tournament Info', 'manage.tournaments.edit'],
        'import_settings' => ['Import Settings', 'manage.import-settings.index'],
        'divisions_and_groups' => ['Set up Divisions and Groups', 'manage.divisions.index'],
        'mats' => ['Set up Mats', 'manage.mat-setup.index'],
        'bout_numbering' => ['Create Bouting Scheme and Mat Assignment', 'manage.number-schemes.index'],
        'check_in' => ['Check In Wrestlers', 'manage.checkin.index'],
        'bracket_divisions' => ['Bracket Divisions', 'manage.tournaments.show'],
        'bout_divisions' => ['Bout Divisions', 'manage.tournaments.show'],
        'reports' => ['Reports', 'manage.reports.index'],
    ];

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

    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load('checklistItems');
        $this->syncAutoComplete($tournament);
        $tournament->load('checklistItems');
        $steps = $this->buildSteps($tournament);

        return view('manage.checklist.index', [
            'tournament' => $tournament,
            'steps' => $steps,
        ]);
    }

    public function toggle(Request $request, int $tid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $request->validate(['step_key' => 'required|string|in:' . implode(',', TournamentChecklist::stepKeys())]);
        $stepKey = $request->input('step_key');

        $item = TournamentChecklist::firstOrCreate(
            ['tournament_id' => $tid, 'step_key' => $stepKey],
            ['is_completed' => false]
        );
        $item->update(['is_completed' => ! $item->is_completed]);

        return redirect()->route('manage.checklist.index', $tid)->with('success', 'Checklist updated.');
    }

    /**
     * Build steps array with number, title, route, is_completed.
     *
     * @return array<int, array{number: int, key: string, title: string, url: string, is_completed: bool}>
     */
    private function buildSteps(Tournament $tournament): array
    {
        $byKey = $tournament->checklistItems->keyBy('step_key');
        $steps = [];
        $order = TournamentChecklist::stepKeys();
        foreach ($order as $i => $key) {
            $item = $byKey->get($key);
            $config = self::STEP_CONFIG[$key] ?? [$key, 'manage.tournaments.show'];
            $steps[] = [
                'number' => $i + 1,
                'key' => $key,
                'title' => $config[0],
                'url' => route($config[1], $tournament->id),
                'is_completed' => $item ? $item->is_completed : false,
            ];
        }
        return $steps;
    }

    /**
     * Auto-check steps based on simple data rules; only set to completed, never uncheck.
     */
    private function syncAutoComplete(Tournament $tournament): void
    {
        $tid = $tournament->id;
        $auto = [];

        if ($tournament->updated_at && $tournament->created_at && $tournament->updated_at->gt($tournament->created_at)) {
            $auto['update_tournament_info'] = true;
        }
        if (Division::where('Tournament_Id', $tid)->exists()) {
            $auto['divisions_and_groups'] = true;
        }
        if ($tournament->tournamentMats()->exists()) {
            $auto['mats'] = true;
        }
        if ($tournament->tournamentMats()->exists() && BoutNumberScheme::where('tournament_id', $tid)->exists()) {
            $auto['bout_numbering'] = true;
        }
        $checkedIn = TournamentWrestler::where('Tournament_id', $tid)->where('checked_in', 1)->exists();
        if ($checkedIn) {
            $auto['check_in'] = true;
        }
        if ($tournament->divisions()->where('Bracketed', 1)->exists()) {
            $auto['bracket_divisions'] = true;
        }
        if ($tournament->divisions()->where('bouted', 1)->exists()) {
            $auto['bout_divisions'] = true;
        }

        foreach ($auto as $key => $completed) {
            if (! $completed) {
                continue;
            }
            TournamentChecklist::updateOrCreate(
                ['tournament_id' => $tid, 'step_key' => $key],
                ['is_completed' => true]
            );
        }
    }
}

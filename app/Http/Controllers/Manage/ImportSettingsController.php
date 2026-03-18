<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentChecklist;
use App\Services\ImportSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportSettingsController extends Controller
{
    public function __construct(
        private ImportSettingsService $importService
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
     * Admin-only tournament selection page: list tournaments the user can manage (excluding current).
     */
    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $user = $request->user();
        $tournaments = $user->isAdmin()
            ? Tournament::where('id', '!=', $tid)->orderBy('TournamentDate', 'desc')->get()
            : $user->managedTournaments()->where('tournaments.id', '!=', $tid)->orderBy('TournamentDate', 'desc')->get();

        $name = $request->query('name');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $hasFilters = ($name !== null && $name !== '') || ($dateFrom !== null && $dateFrom !== '') || ($dateTo !== null && $dateTo !== '');

        if (! $hasFilters) {
            $today = now()->startOfDay();
            $tournaments = $tournaments->filter(function ($t) use ($today) {
                return ! $t->pending_approval
                    && $t->TournamentDate
                    && $t->TournamentDate->startOfDay()->gte($today);
            });
        }
        if ($name !== null && $name !== '') {
            $tournaments = $tournaments->filter(fn ($t) => stripos($t->TournamentName, $name) !== false);
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $tournaments = $tournaments->filter(fn ($t) => $t->TournamentDate && $t->TournamentDate->format('Y-m-d') >= $dateFrom);
        }
        if ($dateTo !== null && $dateTo !== '') {
            $tournaments = $tournaments->filter(fn ($t) => $t->TournamentDate && $t->TournamentDate->format('Y-m-d') <= $dateTo);
        }

        return view('manage.import-settings.index', [
            'tournament' => $tournament,
            'tournaments' => $tournaments->values(),
            'filters' => [
                'name' => $name,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Show checkboxes to choose what to copy from source tournament.
     * Bout Numbering is only available when Mats is selected (or target already has mats).
     */
    public function from(Request $request, int $tid, int $sourceTid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $source = Tournament::findOrFail($sourceTid);
        $user = $request->user();
        $canManageSource = $user->isAdmin() || $user->managedTournaments()->where('tournaments.id', $sourceTid)->exists();
        if (! $canManageSource) {
            abort(403, 'You cannot import from that tournament.');
        }
        if ($sourceTid === $tid) {
            return redirect()->route('manage.import-settings.index', $tid)->with('error', 'Cannot import from the same tournament.');
        }

        $targetHasMats = $tournament->tournamentMats()->exists();
        $sourceHasMats = $source->tournamentMats()->exists();

        return view('manage.import-settings.from', [
            'tournament' => $tournament,
            'source' => $source,
            'targetHasMats' => $targetHasMats,
            'sourceHasMats' => $sourceHasMats,
        ]);
    }

    /**
     * Run the import based on selected options.
     */
    public function store(Request $request, int $tid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $request->validate([
            'source_tid' => 'required|integer|exists:tournaments,id',
            'copy_divisions' => 'nullable|in:1',
            'copy_mats' => 'nullable|in:1',
            'copy_bout_numbering' => 'nullable|in:1',
        ]);
        $sourceTid = (int) $request->input('source_tid');
        if ($sourceTid === $tid) {
            return redirect()->route('manage.import-settings.index', $tid)->with('error', 'Cannot import from the same tournament.');
        }
        $user = $request->user();
        $canManageSource = $user->isAdmin() || $user->managedTournaments()->where('tournaments.id', $sourceTid)->exists();
        if (! $canManageSource) {
            return redirect()->route('manage.import-settings.index', $tid)->with('error', 'You cannot import from that tournament.');
        }

        $copyDivisions = $request->boolean('copy_divisions');
        $copyMats = $request->boolean('copy_mats');
        $copyBoutNumbering = $request->boolean('copy_bout_numbering');

        if ($copyBoutNumbering && ! $copyMats) {
            $targetHasMats = $tournament->tournamentMats()->exists();
            if (! $targetHasMats) {
                return redirect()->route('manage.import-settings.from', [$tid, $sourceTid])
                    ->with('error', 'Bout Numbering requires Mats. Either select "Copy Mats" or ensure this tournament already has mats configured.')
                    ->withInput();
            }
        }

        $divisionMap = [];
        if ($copyDivisions) {
            $divisionMap = $this->importService->copyDivisionsAndGroups($sourceTid, $tid);
        }
        if ($copyMats) {
            $this->importService->copyMats($sourceTid, $tid);
        }
        if ($copyBoutNumbering) {
            $this->importService->copyBoutNumbering($sourceTid, $tid, $divisionMap);
        }

        $done = [];
        if ($copyDivisions) {
            $done[] = 'Divisions & Groups';
        }
        if ($copyMats) {
            $done[] = 'Mats';
        }
        if ($copyBoutNumbering) {
            $done[] = 'Bout Numbering';
        }

        if (empty($done)) {
            return redirect()->route('manage.import-settings.from', [$tid, $sourceTid])
                ->with('error', 'Select at least one option to copy.');
        }

        TournamentChecklist::updateOrCreate(
            ['tournament_id' => $tid, 'step_key' => 'import_settings'],
            ['is_completed' => true]
        );
        if ($copyDivisions) {
            TournamentChecklist::updateOrCreate(
                ['tournament_id' => $tid, 'step_key' => 'divisions_and_groups'],
                ['is_completed' => true]
            );
        }
        if ($copyMats) {
            TournamentChecklist::updateOrCreate(
                ['tournament_id' => $tid, 'step_key' => 'mats'],
                ['is_completed' => true]
            );
        }
        if ($copyBoutNumbering) {
            TournamentChecklist::updateOrCreate(
                ['tournament_id' => $tid, 'step_key' => 'bout_numbering'],
                ['is_completed' => true]
            );
        }

        return redirect()->route('manage.import-settings.index', $tid)
            ->with('success', 'Imported: ' . implode(', ', $done) . '.');
    }
}

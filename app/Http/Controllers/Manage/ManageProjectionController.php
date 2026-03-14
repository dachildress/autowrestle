<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Division;
use App\Models\ProjectionView;
use App\Models\ProjectionViewGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManageProjectionController extends Controller
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
     * List projection views for the tournament.
     */
    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $views = ProjectionView::where('tournament_id', $tid)->orderBy('name')->get();
        return view('manage.projection.index', [
            'tournament' => $tournament,
            'views' => $views,
        ]);
    }

    /**
     * Form to create a new projection view.
     */
    public function create(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $tournament->load(['divisions' => fn ($q) => $q->orderBy('id'), 'divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $tid)->orderBy('id')]);
        $groups = $this->groupsList($tournament);
        return view('manage.projection.create', [
            'tournament' => $tournament,
            'groups' => $groups,
        ]);
    }

    /**
     * Store a new projection view.
     */
    public function store(Request $request, int $tid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $valid = $request->validate([
            'name' => 'required|string|max:100',
            'wrestlers_per_mat' => 'required|integer|min:1|max:20',
            'groups' => 'array',
            'groups.*' => 'string',
        ]);
        $view = ProjectionView::create([
            'tournament_id' => $tid,
            'name' => $valid['name'],
            'wrestlers_per_mat' => (int) $valid['wrestlers_per_mat'],
        ]);
        $this->syncGroups($view, $request->input('groups', []));
        return redirect()->route('manage.projection.index', $tid)->with('success', 'Projection view created.');
    }

    /**
     * Form to edit a projection view.
     */
    public function edit(Request $request, int $tid, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $view = ProjectionView::where('tournament_id', $tid)->findOrFail($id);
        $tournament->load(['divisions' => fn ($q) => $q->orderBy('id'), 'divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $tid)->orderBy('id')]);
        $groups = $this->groupsList($tournament);
        $selectedKeys = $view->projectionViewGroups()->get()->map(fn ($g) => $g->group_id . '_' . $g->division_id)->all();
        return view('manage.projection.edit', [
            'tournament' => $tournament,
            'view' => $view,
            'groups' => $groups,
            'selectedKeys' => $selectedKeys,
        ]);
    }

    /**
     * Update a projection view.
     */
    public function update(Request $request, int $tid, int $id): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $view = ProjectionView::where('tournament_id', $tid)->findOrFail($id);
        $valid = $request->validate([
            'name' => 'required|string|max:100',
            'wrestlers_per_mat' => 'required|integer|min:1|max:20',
            'groups' => 'array',
            'groups.*' => 'string',
        ]);
        $view->update([
            'name' => $valid['name'],
            'wrestlers_per_mat' => (int) $valid['wrestlers_per_mat'],
        ]);
        $this->syncGroups($view, $request->input('groups', []));
        return redirect()->route('manage.projection.index', $tid)->with('success', 'Projection view updated.');
    }

    /**
     * Delete a projection view.
     */
    public function destroy(Request $request, int $tid, int $id): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $view = ProjectionView::where('tournament_id', $tid)->findOrFail($id);
        $view->delete();
        return redirect()->route('manage.projection.index', $tid)->with('success', 'Projection view deleted.');
    }

    /**
     * Fullscreen display for projection (coming up). Auto-refresh 10s.
     */
    public function display(Request $request, int $tid, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $view = ProjectionView::where('tournament_id', $tid)->with('projectionViewGroups')->findOrFail($id);
        $matsData = $this->buildMatsData($tid, $view);
        return view('manage.projection.display', [
            'tournament' => $tournament,
            'view' => $view,
            'matsData' => $matsData,
        ]);
    }

    private function groupsList(Tournament $tournament): array
    {
        $list = [];
        foreach ($tournament->divisions as $div) {
            foreach ($div->divGroups as $g) {
                $list[] = (object) [
                    'key' => $g->id . '_' . $g->Division_id,
                    'group_id' => $g->id,
                    'division_id' => $g->Division_id,
                    'label' => $div->DivisionName . ' – ' . $g->Name,
                ];
            }
        }
        return $list;
    }

    private function syncGroups(ProjectionView $view, array $groupKeys): void
    {
        ProjectionViewGroup::where('projection_view_id', $view->id)->delete();
        foreach ($groupKeys as $key) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            $parts = explode('_', $key, 2);
            if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
                continue;
            }
            ProjectionViewGroup::create([
                'projection_view_id' => $view->id,
                'group_id' => (int) $parts[0],
                'division_id' => (int) $parts[1],
            ]);
        }
    }

    /**
     * Mats that belong to the selected groups (from each division's StartingMat/TotalMats).
     *
     * @return list<int>
     */
    private function getMatsForView(ProjectionView $view): array
    {
        $divisionIds = $view->projectionViewGroups()->distinct()->pluck('division_id')->all();
        if (empty($divisionIds)) {
            return [];
        }
        $divisions = Division::whereIn('id', $divisionIds)->get();
        $mats = [];
        foreach ($divisions as $d) {
            $start = (int) $d->StartingMat;
            $total = (int) $d->TotalMats;
            for ($i = 0; $i < $total; $i++) {
                $mats[$start + $i] = true;
            }
        }
        ksort($mats);
        return array_keys($mats);
    }

    /**
     * Build per-mat rows: mat_number => [ { row_type, bout } | null for empty ].
     * Each mat shows its own incomplete bouts in order (round, bout id). Filter by the view's
     * divisions only (not by bracket) so all bouts on that mat in those divisions appear in sequence.
     *
     * @return array<int, list<object|null>>
     */
    private function buildMatsData(int $tid, ProjectionView $view): array
    {
        $divisionIds = $view->projectionViewGroups()->distinct()->pluck('division_id')->all();
        if (empty($divisionIds)) {
            return [];
        }
        $matsForView = $this->getMatsForView($view);
        if (empty($matsForView)) {
            return [];
        }
        $perMat = (int) $view->wrestlers_per_mat;

        $matsData = [];
        foreach ($matsForView as $mat) {
            $query = Bout::where('Tournament_Id', $tid)
                ->where('mat_number', $mat)
                ->where('completed', false)
                ->whereIn('Division_Id', $divisionIds);
            $boutRows = $query->orderBy('round')->orderBy('id')->get();
            // Unique bout ids in order (each bout has 2 rows)
            $boutIds = [];
            $seen = [];
            foreach ($boutRows as $row) {
                if (!isset($seen[$row->id])) {
                    $seen[$row->id] = true;
                    $boutIds[] = $row->id;
                }
            }
            $boutIds = array_slice($boutIds, 0, $perMat);
            $rows = [];
            foreach (array_values($boutIds) as $index => $boutId) {
                $rowType = $index === 0 ? 'current' : ($index === 1 ? 'next' : 'upcoming');
                $boutInfo = $this->boutInfo($tid, $boutId);
                if ($boutInfo !== null) {
                    $rows[] = (object) ['row_type' => $rowType, 'bout' => $boutInfo];
                }
            }
            while (count($rows) < $perMat) {
                $rows[] = null;
            }
            $matsData[$mat] = $rows;
        }
        return $matsData;
    }

    private function boutInfo(int $tid, int $boutId): ?object
    {
        $wrestlers = TournamentWrestler::select('tournamentwrestlers.id as wr_id', 'tournamentwrestlers.wr_first_name', 'tournamentwrestlers.wr_last_name', 'tournamentwrestlers.wr_weight', 'tournamentwrestlers.wr_club', 'brackets.wr_pos', 'bouts.round', 'divisions.DivisionName')
            ->join('bouts', function ($j) use ($tid) {
                $j->on('tournamentwrestlers.id', '=', 'bouts.Wrestler_Id')->where('bouts.Tournament_Id', '=', $tid);
            })
            ->join('brackets', function ($j) use ($tid) {
                $j->on('brackets.wr_Id', '=', 'bouts.Wrestler_Id')->on('brackets.id', '=', 'bouts.Bracket_Id')->where('brackets.Tournament_Id', '=', $tid);
            })
            ->leftJoin('divisions', 'bouts.Division_Id', '=', 'divisions.id')
            ->where('bouts.id', $boutId)
            ->where('bouts.Tournament_Id', $tid)
            ->orderBy('brackets.wr_pos')
            ->get();
        if ($wrestlers->count() < 2) {
            return null;
        }
        $weightQuery = TournamentWrestler::select(DB::raw('MIN(tournamentwrestlers.wr_weight) as low, MAX(tournamentwrestlers.wr_weight) as high'))
            ->join('brackets', function ($j) use ($tid) {
                $j->on('tournamentwrestlers.id', '=', 'brackets.wr_Id')->where('brackets.Tournament_Id', '=', $tid);
            })
            ->join('bouts', function ($j) use ($tid, $boutId) {
                $j->on('bouts.Bracket_Id', '=', 'brackets.id')->where('bouts.Tournament_Id', '=', $tid)->where('bouts.id', '=', $boutId);
            })
            ->where('tournamentwrestlers.Tournament_id', $tid)
            ->first();
        $weight = $weightQuery ? ($weightQuery->low . ' – ' . $weightQuery->high) : '–';
        return (object) [
            'id' => $boutId,
            'round' => $wrestlers[0]->round,
            'wr1' => $wrestlers[0],
            'wr2' => $wrestlers[1],
            'weight' => $weight,
            'group' => $wrestlers[0]->DivisionName ?? '–',
        ];
    }
}

<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Bracket;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ManageTournamentController extends Controller
{
    private function authorizeTournament(Request $request, int $id): Tournament
    {
        $tournament = Tournament::findOrFail($id);
        $user = $request->user();
        if ($user->isAdmin()) {
            return $tournament;
        }
        if ($tournament->users()->where('User_id', $user->id)->exists()) {
            return $tournament;
        }
        abort(403, 'You cannot manage this tournament.');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $tournaments = $user->isAdmin()
            ? Tournament::orderBy('TournamentDate', 'desc')->get()
            : $user->managedTournaments()->orderBy('TournamentDate', 'desc')->get();
        return view('manage.tournaments.index', compact('tournaments'));
    }

    public function show(Request $request, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $id);
        $tournament->load(['divisions.divGroups', 'tournamentWrestlers']);
        return view('manage.tournaments.show', compact('tournament'));
    }

    /**
     * Show Edit Tournament form (Edit Info).
     */
    public function edit(Request $request, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $id);
        return view('manage.tournaments.edit', compact('tournament'));
    }

    /**
     * Update tournament info (name, dates, message, flyer, AllowDouble, ViewWrestlers).
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $id);
        $request->validate([
            'TournamentName' => 'required|string|max:100',
            'TournamentDate' => 'required|date',
            'OpenDate' => 'required|date',
            'message' => 'nullable|string|max:2000',
            'flyer' => 'nullable|file|mimes:pdf|max:2560',
        ]);

        $tournament->TournamentName = $request->input('TournamentName');
        $tournament->TournamentDate = $request->input('TournamentDate');
        $tournament->OpenDate = $request->input('OpenDate');
        $tournament->message = $request->input('message');
        $tournament->AllowDouble = $request->boolean('AllowDouble') ? '1' : '0';
        $tournament->ViewWrestlers = $request->boolean('ViewWrestlers') ? 1 : 0;

        if ($request->hasFile('flyer')) {
            $dir = public_path('flyers');
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            if ($tournament->link && File::exists($dir . '/' . $tournament->link)) {
                File::delete($dir . '/' . $tournament->link);
            }
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', $tournament->TournamentName) . '-' . $tournament->TournamentDate->format('Y-m-d') . '.pdf';
            $request->file('flyer')->move($dir, $safeName);
            $tournament->link = $safeName;
        }

        $tournament->save();

        return redirect()->route('manage.view.summary', $id)->with('success', 'Tournament updated.');
    }

    /**
     * Print scan sheet: one sheet for the entire tournament (instructions + link to find bouts).
     */
    public function printScanSheet(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $searchUrl = url(route('mybouts.search', $tid));

        return view('manage.scansheet.print', [
            'tournament' => $tournament,
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * View summary: division boxes with Wrestlers, Bouts, Mats, Brackets, Teams.
     */
    public function viewSummary(Request $request, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $id);
        $tournament->load(['divisions' => fn ($q) => $q->orderBy('id'), 'divisions.divGroups' => fn ($q) => $q->where('Tournament_Id', $id)->orderBy('id')]);

        $divisionStats = [];
        foreach ($tournament->divisions as $div) {
            $wrestlers = TournamentWrestler::query()
                ->join('divgroups', function ($j) use ($id, $div) {
                    $j->on('tournamentwrestlers.group_id', '=', 'divgroups.id')
                        ->where('divgroups.Tournament_Id', '=', $id)
                        ->where('divgroups.Division_id', '=', $div->id);
                })
                ->where('tournamentwrestlers.Tournament_id', $id)
                ->count();
            $bouts = (int) Bout::where('Tournament_Id', $id)->where('Division_Id', $div->id)->selectRaw('count(distinct id) as c')->value('c');
            $bracketCount = (int) Bracket::where('Tournament_Id', $id)->where('Division_Id', $div->id)->selectRaw('count(distinct id) as c')->value('c');
            $teams = (int) TournamentWrestler::query()
                ->join('divgroups', function ($j) use ($id, $div) {
                    $j->on('tournamentwrestlers.group_id', '=', 'divgroups.id')
                        ->where('divgroups.Tournament_Id', '=', $id)
                        ->where('divgroups.Division_id', '=', $div->id);
                })
                ->where('tournamentwrestlers.Tournament_id', $id)
                ->selectRaw('count(distinct tournamentwrestlers.wr_club) as c')
                ->value('c');

            $divisionStats[] = (object) [
                'division' => $div,
                'wrestlers' => $wrestlers,
                'bouts' => $bouts,
                'mats' => $div->TotalMats,
                'brackets' => $bracketCount,
                'teams' => $teams,
            ];
        }

        return view('manage.view.summary', [
            'tournament' => $tournament,
            'divisionStats' => $divisionStats,
        ]);
    }
}

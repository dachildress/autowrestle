<?php

namespace App\Http\Controllers;

use App\Models\Bout;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function current(): View
    {
        $tournaments = Tournament::upcomingAndOpen()->orderBy('TournamentDate', 'asc')->get();
        return view('tournaments.list', compact('tournaments'));
    }

    public function show(int $id): View
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->load(['divisions', 'tournamentWrestlers.wrestler']);
        return view('tournaments.show', compact('tournament'));
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

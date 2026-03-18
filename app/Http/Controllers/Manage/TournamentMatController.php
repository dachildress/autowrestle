<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentMatController extends Controller
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

    public function index(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $mats = TournamentMat::where('tournament_id', $tid)->orderBy('mat_number')->get();
        return view('manage.mat-setup.index', compact('tournament', 'mats'));
    }

    public function create(Request $request, int $tid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $nextNumber = TournamentMat::where('tournament_id', $tid)->max('mat_number') + 1;
        if ($nextNumber < 1) {
            $nextNumber = 1;
        }
        return view('manage.mat-setup.create', compact('tournament', 'nextNumber'));
    }

    public function store(Request $request, int $tid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $valid = $request->validate([
            'mat_number' => 'required|integer|min:1|max:255',
            'name' => 'nullable|string|max:100',
            'constraint' => 'nullable|string|max:100',
        ]);
        if (TournamentMat::where('tournament_id', $tid)->where('mat_number', $valid['mat_number'])->exists()) {
            return redirect()->back()->withInput()->withErrors(['mat_number' => 'A mat with this number already exists.']);
        }
        TournamentMat::create([
            'tournament_id' => $tid,
            'mat_number' => (int) $valid['mat_number'],
            'name' => $valid['name'] ?: null,
            'constraint' => $valid['constraint'] ?: null,
        ]);
        return redirect()->route('manage.mat-setup.index', $tid)->with('success', 'Mat added.');
    }

    public function edit(Request $request, int $tid, int $mid): View
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $mat = TournamentMat::where('tournament_id', $tid)->where('id', $mid)->firstOrFail();
        return view('manage.mat-setup.edit', compact('tournament', 'mat'));
    }

    public function update(Request $request, int $tid, int $mid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $mat = TournamentMat::where('tournament_id', $tid)->where('id', $mid)->firstOrFail();
        $valid = $request->validate([
            'mat_number' => 'required|integer|min:1|max:255',
            'name' => 'nullable|string|max:100',
            'constraint' => 'nullable|string|max:100',
        ]);
        $existing = TournamentMat::where('tournament_id', $tid)->where('mat_number', $valid['mat_number'])->where('id', '!=', $mid)->first();
        if ($existing) {
            return redirect()->back()->withInput()->withErrors(['mat_number' => 'A mat with this number already exists.']);
        }
        $mat->update([
            'mat_number' => (int) $valid['mat_number'],
            'name' => $valid['name'] ?: null,
            'constraint' => $valid['constraint'] ?: null,
        ]);
        return redirect()->route('manage.mat-setup.index', $tid)->with('success', 'Mat updated.');
    }

    public function destroy(Request $request, int $tid, int $mid): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $mat = TournamentMat::where('tournament_id', $tid)->where('id', $mid)->firstOrFail();
        $mat->delete();
        return redirect()->route('manage.mat-setup.index', $tid)->with('success', 'Mat removed.');
    }
}

<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\ChallengeRequest;
use App\Models\Tournament;
use App\Notifications\ChallengeRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Director flow: list challenge requests, review, approve (with mat), or decline.
 */
class ManageChallengeRequestController extends Controller
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

    public function index(Request $request, int $tid): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        if (! $tournament->enable_challenge_matches) {
            return redirect()->route('manage.tournaments.show', $tid)->with('error', 'Challenge matches are not enabled for this tournament.');
        }

        $pending = ChallengeRequest::where('tournament_id', $tid)
            ->where('status', ChallengeRequest::STATUS_ACCEPTED_PENDING_DIRECTOR)
            ->with(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'challengerUser', 'challengedUser'])
            ->orderByDesc('accepted_at')
            ->get();

        $other = ChallengeRequest::where('tournament_id', $tid)
            ->whereIn('status', [
                ChallengeRequest::STATUS_PENDING_ACCEPTANCE,
                ChallengeRequest::STATUS_DECLINED_BY_PARENT,
                ChallengeRequest::STATUS_DECLINED_BY_DIRECTOR,
                ChallengeRequest::STATUS_SCHEDULED,
            ])
            ->with(['challengerTournamentWrestler', 'challengedTournamentWrestler'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('manage.challenge-requests.index', [
            'tournament' => $tournament,
            'pending' => $pending,
            'other' => $other,
        ]);
    }

    public function show(Request $request, int $tid, int $id): View|RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        if (! $tournament->enable_challenge_matches) {
            return redirect()->route('manage.tournaments.show', $tid)->with('error', 'Challenge matches are not enabled.');
        }

        $challenge = ChallengeRequest::where('tournament_id', $tid)
            ->where('id', $id)
            ->with(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'challengerUser', 'challengedUser'])
            ->firstOrFail();

        $mats = $tournament->getConfiguredMatNumbers();
        if (empty($mats)) {
            $mats = [1, 2, 3, 4];
        }

        return view('manage.challenge-requests.show', [
            'tournament' => $tournament,
            'challenge' => $challenge,
            'mats' => $mats,
        ]);
    }

    public function approve(Request $request, int $tid, int $id): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $tid);
        $challenge = ChallengeRequest::where('tournament_id', $tid)
            ->where('id', $id)
            ->where('status', ChallengeRequest::STATUS_ACCEPTED_PENDING_DIRECTOR)
            ->firstOrFail();

        $valid = $request->validate([
            'mat_number' => 'required|integer|min:1',
        ]);
        $matNumber = (int) $valid['mat_number'];
        $mats = $tournament->getConfiguredMatNumbers();
        if (! empty($mats) && ! in_array($matNumber, $mats, true)) {
            return redirect()->back()->with('error', 'Invalid mat. Choose from configured mats.')->withInput();
        }

        $maxId = Bout::where('Tournament_Id', $tid)->max('id');
        $newBoutId = $maxId ? (int) $maxId + 1 : 1;

        $challengerTw = $challenge->challengerTournamentWrestler;
        $challengedTw = $challenge->challengedTournamentWrestler;
        $divisionId = $challengerTw->division_id ?? $challengedTw->division_id;
        if (! $divisionId) {
            $firstDivision = $tournament->divisions()->orderBy('id')->first();
            $divisionId = $firstDivision ? $firstDivision->id : 1;
        }

        Bout::insert([
            [
                'id' => $newBoutId,
                'Wrestler_Id' => $challengerTw->id,
                'Bracket_Id' => 0,
                'mat_number' => $matNumber,
                'round' => 1,
                'Tournament_Id' => $tid,
                'Division_Id' => $divisionId,
                'challenge_request_id' => $challenge->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $newBoutId,
                'Wrestler_Id' => $challengedTw->id,
                'Bracket_Id' => 0,
                'mat_number' => $matNumber,
                'round' => 1,
                'Tournament_Id' => $tid,
                'Division_Id' => $divisionId,
                'challenge_request_id' => $challenge->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $challenge->update([
            'status' => ChallengeRequest::STATUS_SCHEDULED,
            'mat_number' => $matNumber,
            'bout_id' => $newBoutId,
            'director_acted_at' => now(),
        ]);
        $challenge->load(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'tournament', 'challengerUser', 'challengedUser']);
        $notification = new ChallengeRequestNotification($challenge, ChallengeRequestNotification::TYPE_MATCH_SCHEDULED, $matNumber);
        if ($challenge->challengerUser) {
            $challenge->challengerUser->notify($notification);
        }
        if ($challenge->challengedUser) {
            $challenge->challengedUser->notify($notification);
        }

        return redirect()->route('manage.challenge-requests.index', $tid)->with('success', 'Challenge approved. Match scheduled on Mat ' . $matNumber . '. Both parents will be notified.');
    }

    public function decline(Request $request, int $tid, int $id): RedirectResponse
    {
        $this->authorizeTournament($request, $tid);
        $challenge = ChallengeRequest::where('tournament_id', $tid)
            ->where('id', $id)
            ->where('status', ChallengeRequest::STATUS_ACCEPTED_PENDING_DIRECTOR)
            ->firstOrFail();

        $challenge->update([
            'status' => ChallengeRequest::STATUS_DECLINED_BY_DIRECTOR,
            'director_acted_at' => now(),
            'director_notes' => $request->input('director_notes'),
        ]);
        $challenge->load(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'tournament', 'challengerUser', 'challengedUser']);
        $notification = new ChallengeRequestNotification($challenge, ChallengeRequestNotification::TYPE_DIRECTOR_DECLINED);
        if ($challenge->challengerUser) {
            $challenge->challengerUser->notify($notification);
        }
        if ($challenge->challengedUser) {
            $challenge->challengedUser->notify($notification);
        }

        return redirect()->route('manage.challenge-requests.index', $tid)->with('success', 'Challenge declined.');
    }
}

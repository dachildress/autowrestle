<?php

namespace App\Http\Controllers;

use App\Models\ChallengeRequest;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Notifications\ChallengeRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Parent-facing challenge match flow: send challenge, accept/decline incoming.
 */
class ChallengeMatchController extends Controller
{
    /** List: my wrestlers in this tournament, sent challenges, incoming challenges. */
    public function index(Request $request, int $id): View|RedirectResponse
    {
        $tournament = Tournament::find($id);
        if (! $tournament || ! $this->tournamentAllowsChallenge($tournament)) {
            return redirect()->route('tournaments.show', $id)->with('error', 'Challenge matches are not available for this tournament.');
        }

        $user = $request->user();
        $myWrestlerIds = $user->wrestlers()->pluck('id');
        $myTournamentWrestlers = TournamentWrestler::where('Tournament_id', $id)
            ->whereIn('Wrestler_Id', $myWrestlerIds)
            ->with('wrestler')
            ->orderBy('wr_last_name')
            ->orderBy('wr_first_name')
            ->get();

        $sent = ChallengeRequest::where('tournament_id', $id)
            ->where('challenger_user_id', $user->id)
            ->with(['challengerTournamentWrestler', 'challengedTournamentWrestler'])
            ->orderByDesc('created_at')
            ->get();

        $incoming = ChallengeRequest::where('tournament_id', $id)
            ->where('challenged_user_id', $user->id)
            ->where('status', ChallengeRequest::STATUS_PENDING_ACCEPTANCE)
            ->with(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'challengerUser'])
            ->orderByDesc('created_at')
            ->get();

        return view('challenge.index', [
            'tournament' => $tournament,
            'myTournamentWrestlers' => $myTournamentWrestlers,
            'sent' => $sent,
            'incoming' => $incoming,
        ]);
    }

    /** Single-page challenge request: select your wrestler (dropdown), search and pick opponent (same division only). */
    public function create(Request $request, int $id): View|RedirectResponse
    {
        $tournament = Tournament::find($id);
        if (! $tournament || ! $this->tournamentAllowsChallenge($tournament)) {
            return redirect()->route('tournaments.show', $id)->with('error', 'Challenge matches are not available.');
        }

        $user = $request->user();
        $myWrestlerIds = $user->wrestlers()->pluck('id');
        $myTournamentWrestlers = TournamentWrestler::where('Tournament_id', $id)
            ->whereIn('Wrestler_Id', $myWrestlerIds)
            ->with('wrestler')
            ->orderBy('wr_last_name')
            ->orderBy('wr_first_name')
            ->get();

        if ($myTournamentWrestlers->isEmpty()) {
            return redirect()->route('challenge.index', $id)->with('error', 'You have no wrestlers registered in this tournament.');
        }

        $challengerTwId = (int) $request->query('challenger_tournament_wrestler_id');
        $challengerTw = null;
        $opponents = collect();

        if ($challengerTwId > 0) {
            $challengerTw = TournamentWrestler::where('id', $challengerTwId)
                ->where('Tournament_id', $id)
                ->whereIn('Wrestler_Id', $myWrestlerIds)
                ->first();
            if ($challengerTw) {
                $search = trim((string) $request->query('q'));
                $opponentsQuery = TournamentWrestler::where('Tournament_id', $id)
                    ->where('id', '!=', $challengerTw->id)
                    ->whereNotIn('Wrestler_Id', $myWrestlerIds);
                if ($challengerTw->division_id !== null && $challengerTw->division_id !== '') {
                    $opponentsQuery->where('division_id', $challengerTw->division_id);
                } else {
                    $opponentsQuery->whereNull('division_id');
                }

                if ($search !== '') {
                    $opponentsQuery->where(function ($q) use ($search) {
                        $q->where('wr_first_name', 'like', '%' . $search . '%')
                            ->orWhere('wr_last_name', 'like', '%' . $search . '%');
                    });
                }
                $opponents = $opponentsQuery->orderBy('wr_last_name')->orderBy('wr_first_name')->limit(100)->get();
            }
        }

        $search = trim((string) $request->query('q', ''));

        return view('challenge.create', [
            'tournament' => $tournament,
            'myTournamentWrestlers' => $myTournamentWrestlers,
            'challengerTw' => $challengerTw,
            'opponents' => $opponents,
            'search' => $search,
        ]);
    }

    /** Legacy: redirect to single-page create with query params. */
    public function selectOpponent(Request $request, int $id): RedirectResponse
    {
        $params = array_filter([
            'challenger_tournament_wrestler_id' => $request->query('challenger_tournament_wrestler_id'),
            'q' => $request->query('q'),
        ]);
        return redirect()->route('challenge.create', array_merge([$id], $params));
    }

    /** Store new challenge request. */
    public function store(Request $request, int $id): RedirectResponse
    {
        $tournament = Tournament::find($id);
        if (! $tournament || ! $this->tournamentAllowsChallenge($tournament)) {
            return redirect()->route('tournaments.show', $id)->with('error', 'Challenge matches are not available.');
        }

        $valid = $request->validate([
            'challenger_tournament_wrestler_id' => 'required|integer',
            'challenged_tournament_wrestler_id' => 'required|integer',
        ]);

        $user = $request->user();
        $myWrestlerIds = $user->wrestlers()->pluck('id');

        $challengerTw = TournamentWrestler::where('id', $valid['challenger_tournament_wrestler_id'])
            ->where('Tournament_id', $id)
            ->whereIn('Wrestler_Id', $myWrestlerIds)
            ->first();
        if (! $challengerTw) {
            return redirect()->route('challenge.create', $id)->with('error', 'Invalid challenger.');
        }

        $challengedTw = TournamentWrestler::where('id', $valid['challenged_tournament_wrestler_id'])
            ->where('Tournament_id', $id)
            ->whereNotIn('Wrestler_Id', $myWrestlerIds)
            ->first();
        if (! $challengedTw) {
            return redirect()->to(route('challenge.create', $id) . '?' . http_build_query(['challenger_tournament_wrestler_id' => $challengerTw->id]))->with('error', 'Invalid opponent.');
        }

        $challengedUserId = $challengedTw->getParentUserId();
        if (! $challengedUserId) {
            return redirect()->back()->with('error', 'That wrestler does not have a parent account and cannot receive challenges.');
        }

        $exists = ChallengeRequest::where('tournament_id', $id)
            ->where('challenger_tournament_wrestler_id', $challengerTw->id)
            ->where('challenged_tournament_wrestler_id', $challengedTw->id)
            ->whereIn('status', [ChallengeRequest::STATUS_PENDING_ACCEPTANCE, ChallengeRequest::STATUS_ACCEPTED_PENDING_DIRECTOR])
            ->exists();
        if ($exists) {
            return redirect()->route('challenge.index', $id)->with('error', 'You already have a pending challenge for this matchup.');
        }

        $challenge = ChallengeRequest::create([
            'tournament_id' => $id,
            'challenger_tournament_wrestler_id' => $challengerTw->id,
            'challenged_tournament_wrestler_id' => $challengedTw->id,
            'challenger_user_id' => $user->id,
            'challenged_user_id' => $challengedUserId,
            'status' => ChallengeRequest::STATUS_PENDING_ACCEPTANCE,
        ]);
        $challenge->load(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'tournament']);
        $challengedUser = \App\Models\User::find($challengedUserId);
        if ($challengedUser) {
            $challengedUser->notify(new ChallengeRequestNotification($challenge, ChallengeRequestNotification::TYPE_CHALLENGE_RECEIVED));
        }
        $user->notify(new ChallengeRequestNotification($challenge, ChallengeRequestNotification::TYPE_CHALLENGE_SENT));

        return redirect()->to(route('challenge.create', $id) . '?' . http_build_query(['challenger_tournament_wrestler_id' => $challengerTw->id]))->with('success', 'Your challenge has been requested.');
    }

    /** Show incoming challenge detail (for accept/decline). */
    public function show(Request $request, int $tournamentId, int $challengeId): View|RedirectResponse
    {
        $tournament = Tournament::find($tournamentId);
        if (! $tournament || ! $this->tournamentAllowsChallenge($tournament)) {
            return redirect()->route('tournaments.show', $tournamentId)->with('error', 'Challenge matches are not available.');
        }

        $user = $request->user();
        $challenge = ChallengeRequest::where('id', $challengeId)
            ->where('tournament_id', $tournamentId)
            ->where('challenged_user_id', $user->id)
            ->where('status', ChallengeRequest::STATUS_PENDING_ACCEPTANCE)
            ->with(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'challengerUser'])
            ->firstOrFail();

        return view('challenge.show', [
            'tournament' => $tournament,
            'challenge' => $challenge,
        ]);
    }

    public function accept(Request $request, int $tournamentId, int $challengeId): RedirectResponse
    {
        $user = $request->user();
        $challenge = ChallengeRequest::where('id', $challengeId)
            ->where('tournament_id', $tournamentId)
            ->where('challenged_user_id', $user->id)
            ->where('status', ChallengeRequest::STATUS_PENDING_ACCEPTANCE)
            ->firstOrFail();

        $challenge->update([
            'status' => ChallengeRequest::STATUS_ACCEPTED_PENDING_DIRECTOR,
            'accepted_at' => now(),
        ]);
        $challenge->load(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'tournament']);
        $challengerUser = $challenge->challengerUser;
        if ($challengerUser) {
            $challengerUser->notify(new ChallengeRequestNotification($challenge, ChallengeRequestNotification::TYPE_CHALLENGE_ACCEPTED));
        }

        return redirect()->route('challenge.index', $tournamentId)->with('success', 'Challenge accepted. Awaiting director approval.');
    }

    public function decline(Request $request, int $tournamentId, int $challengeId): RedirectResponse
    {
        $user = $request->user();
        $challenge = ChallengeRequest::where('id', $challengeId)
            ->where('tournament_id', $tournamentId)
            ->where('challenged_user_id', $user->id)
            ->where('status', ChallengeRequest::STATUS_PENDING_ACCEPTANCE)
            ->firstOrFail();

        $challenge->update(['status' => ChallengeRequest::STATUS_DECLINED_BY_PARENT]);
        $challenge->load(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'tournament']);
        $challengerUser = $challenge->challengerUser;
        if ($challengerUser) {
            $challengerUser->notify(new ChallengeRequestNotification($challenge, ChallengeRequestNotification::TYPE_CHALLENGE_DECLINED));
        }

        return redirect()->route('challenge.index', $tournamentId)->with('success', 'Challenge declined.');
    }

    private function tournamentAllowsChallenge(Tournament $tournament): bool
    {
        return (bool) ($tournament->enable_challenge_matches ?? false);
    }
}

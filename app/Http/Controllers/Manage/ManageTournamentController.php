<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Bout;
use App\Models\Bracket;
use App\Models\Tournament;
use App\Models\User;
use App\Models\TournamentWrestler;
use App\Services\BoutNumberSchemeService;
use App\Mail\TournamentPendingApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
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
        $pendingApproval = $user->isAdmin()
            ? Tournament::where('pending_approval', true)->orderBy('created_at', 'desc')->get()
            : collect();
        return view('manage.tournaments.index', compact('tournaments', 'pendingApproval'));
    }

    public function create(Request $request): View
    {
        return view('manage.tournaments.form', ['tournament' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'TournamentName' => 'required|string|max:100',
            'TournamentDate' => 'required|date',
            'OpenDate' => 'required|date',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:2000',
            'flyer' => 'nullable|file|mimes:pdf|max:2560',
        ]);

        $user = $request->user();
        $isLevelZero = $user->isAdmin(); // accesslevel 0
        $allowDouble = $request->boolean('AllowDouble') ? '1' : '0';
        $viewWrestlers = $request->boolean('ViewWrestlers') ? 1 : 0;

        $tournament = DB::transaction(function () use ($request, $user, $isLevelZero, $allowDouble, $viewWrestlers) {
            $t = Tournament::create([
                'TournamentName' => $request->input('TournamentName'),
                'TournamentDate' => $request->input('TournamentDate'),
                'OpenDate' => $request->input('OpenDate'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'message' => $request->input('message'),
                'AllowDouble' => $allowDouble,
                'status' => 0,
                'pending_approval' => ! $isLevelZero,
                'ViewWrestlers' => $viewWrestlers,
                'usa_number_required' => $request->boolean('usa_number_required'),
                'Type' => 1,
            ]);
            $t->users()->attach($user->id);
            return $t;
        });

        if ($request->hasFile('flyer')) {
            $dir = public_path('flyers');
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', $tournament->TournamentName) . '-' . $tournament->TournamentDate->format('Y-m-d') . '.pdf';
            $request->file('flyer')->move($dir, $safeName);
            $tournament->update(['link' => $safeName]);
        }

        if ($tournament->pending_approval) {
            $admins = User::where('accesslevel', '0')->whereNotNull('email')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new TournamentPendingApproval($tournament));
            }
        }

        $message = $isLevelZero
            ? 'Tournament created and is active.'
            : 'Tournament created. It will appear on the site once approved by an administrator. You can manage it from this page.';
        return redirect()->route('manage.checklist.index', $tournament->id)->with('success', $message);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can approve tournaments.');
        }
        $tournament = Tournament::findOrFail($id);
        if (! $tournament->pending_approval) {
            return redirect()->back()->with('info', 'Tournament is already approved.');
        }
        $tournament->approve();
        return redirect()->back()->with('success', 'Tournament approved and now visible to the public (subject to open/close dates).');
    }

    public function users(Request $request, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $id);
        $tournament->load('users');
        return view('manage.tournaments.users', compact('tournament'));
    }

    public function addUser(Request $request, int $id): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $id);
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->input('email'))->first();
        if (! $user) {
            return redirect()->back()->with('error', 'No user found with that email address.');
        }
        if ($tournament->users()->where('User_id', $user->id)->exists()) {
            return redirect()->back()->with('info', 'That user already has access to this tournament.');
        }
        $tournament->users()->attach($user->id);
        return redirect()->back()->with('success', $user->name . ' has been given access to this tournament.');
    }

    public function removeUser(Request $request, int $id): RedirectResponse
    {
        $tournament = $this->authorizeTournament($request, $id);
        $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $userId = (int) $request->input('user_id');
        if ($userId === $request->user()->id) {
            return redirect()->back()->with('error', 'You cannot remove your own access.');
        }
        $tournament->users()->detach($userId);
        return redirect()->back()->with('success', 'User access removed.');
    }

    public function show(Request $request, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $id);
        $tournament->load(['divisions.divGroups', 'tournamentWrestlers']);
        $schemeService = app(BoutNumberSchemeService::class);
        $divisionHasScheme = [];
        foreach ($tournament->divisions as $div) {
            $divisionHasScheme[$div->id] = $schemeService->divisionHasScheme((int) $tournament->id, (int) $div->id);
        }
        return view('manage.tournaments.show', compact('tournament', 'divisionHasScheme'));
    }

    /**
     * Show Edit Tournament form (same form as create, with tournament pre-filled).
     */
    public function edit(Request $request, int $id): View
    {
        $tournament = $this->authorizeTournament($request, $id);
        return view('manage.tournaments.form', compact('tournament'));
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
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:2000',
            'flyer' => 'nullable|file|mimes:pdf|max:2560',
        ]);

        $tournament->TournamentName = $request->input('TournamentName');
        $tournament->TournamentDate = $request->input('TournamentDate');
        $tournament->OpenDate = $request->input('OpenDate');
        $tournament->city = $request->input('city');
        $tournament->state = $request->input('state');
        $tournament->message = $request->input('message');
        $tournament->AllowDouble = $request->boolean('AllowDouble') ? '1' : '0';
        $tournament->ViewWrestlers = $request->boolean('ViewWrestlers') ? 1 : 0;
        $tournament->usa_number_required = $request->boolean('usa_number_required');

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

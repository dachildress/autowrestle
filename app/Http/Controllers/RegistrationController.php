<?php

namespace App\Http\Controllers;

use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\Wrestler;
use App\Http\Requests\StoreTournamentWrestlerRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function register(Request $request, int $id): View|RedirectResponse
    {
        $tournament = Tournament::findOrFail($id);

        if ((int) $tournament->status === 2) {
            return redirect()->route('tournaments.register.locked');
        }

        $userId = $request->user()->id;
        $wrestlers = Wrestler::where('user_id', $userId)
            ->orderBy('wr_last_name')
            ->orderBy('wr_weight')
            ->get();

        $twEntries = TournamentWrestler::where('Tournament_id', $id)
            ->whereIn('Wrestler_Id', $wrestlers->pluck('id'))
            ->get()
            ->groupBy('Wrestler_Id');

        $statusByWrestler = [];
        foreach ($wrestlers as $w) {
            $entries = $twEntries->get($w->id);
            if ($entries && $entries->contains('bracketed', 1)) {
                $statusByWrestler[$w->id] = 'locked';
            } elseif ($entries && $entries->isNotEmpty()) {
                $statusByWrestler[$w->id] = 'withdraw';
            } else {
                $statusByWrestler[$w->id] = 'add';
            }
        }

        return view('tournaments.register', [
            'tournament' => $tournament,
            'wrestlers' => $wrestlers,
            'statusByWrestler' => $statusByWrestler,
        ]);
    }

    public function locked(): View
    {
        return view('tournaments.locked');
    }

    public function addWrestlerForm(Request $request, int $wid, int $tid): View|RedirectResponse
    {
        $tournament = Tournament::findOrFail($tid);
        $wrestler = Wrestler::where('id', $wid)->where('user_id', $request->user()->id)->firstOrFail();

        if ((int) $tournament->status === 2) {
            return redirect()->route('tournaments.register', $tid)->with('error', 'Tournament is locked.');
        }

        if ($wrestler->wr_dob) {
            $age = Carbon::parse($wrestler->wr_dob)->age;
            if ((int) $wrestler->wr_age !== $age) {
                $wrestler->wr_age = $age;
                $wrestler->save();
            }
        }

        return view('tournaments.addwrestler', [
            'tournament' => $tournament,
            'wrestler' => $wrestler,
        ]);
    }

    public function insertWrestler(StoreTournamentWrestlerRequest $request, int $wid, int $tid): RedirectResponse
    {
        $tournament = Tournament::findOrFail($tid);
        $wrestler = Wrestler::where('id', $wid)->where('user_id', $request->user()->id)->firstOrFail();

        if ((int) $tournament->status === 2) {
            return redirect()->route('tournaments.register', $tid)->with('error', 'Tournament is locked.');
        }

        $weight = (int) $request->input('wr_weight');
        $wrestler->wr_weight = $weight;
        $wrestler->save();

        $age = (int) $wrestler->wr_age;
        $grade = $this->gradeToNumber($wrestler->wr_grade);

        $group = DivGroup::where('Tournament_Id', $tid)
            ->where('MinAge', '<=', $age)
            ->where('MaxAge', '>=', $age)
            ->where('MinGrade', '<=', $grade)
            ->where('MaxGrade', '>=', $grade)
            ->first();

        if (! $group) {
            return back()->withErrors([
                'group' => 'A valid group could not be found for this wrestler\'s age and grade. Please contact the tournament administrator.',
            ])->withInput();
        }

        $brackets = $request->input('brackets', 1);
        $brackets = (int) $brackets === 2 ? 2 : 1;

        for ($i = 0; $i < $brackets; $i++) {
            TournamentWrestler::create([
                'wr_first_name' => $i === 1 ? '*' . $wrestler->wr_first_name : $wrestler->wr_first_name,
                'wr_last_name' => $wrestler->wr_last_name,
                'wr_age' => $wrestler->wr_age,
                'wr_grade' => $wrestler->wr_grade,
                'wr_weight' => $wrestler->wr_weight,
                'wr_club' => $wrestler->wr_club,
                'wr_years' => $wrestler->wr_years,
                'wr_pr' => $i === 1 ? $wrestler->wr_weight + 9 : $wrestler->wr_weight,
                'group_id' => $group->id,
                'Wrestler_Id' => $wrestler->id,
                'Tournament_id' => $tid,
            ]);
        }

        return redirect()->route('tournaments.register', $tid)->with('success', 'Wrestler registered.');
    }

    public function removeWrestler(Request $request, int $wid, int $tid): RedirectResponse
    {
        $tournament = Tournament::findOrFail($tid);
        if ((int) $tournament->status === 2) {
            return redirect()->route('tournaments.register', $tid)->with('error', 'Tournament is locked.');
        }

        $wrestler = Wrestler::where('id', $wid)->where('user_id', $request->user()->id)->firstOrFail();

        $deleted = TournamentWrestler::where('Tournament_id', $tid)
            ->where('Wrestler_Id', $wrestler->id)
            ->delete();

        return redirect()->route('tournaments.register', $tid)
            ->with('success', $deleted ? 'Wrestler withdrawn.' : 'No registration found.');
    }

    private function gradeToNumber(string $grade): int
    {
        $g = trim($grade);
        if (strtoupper($g) === 'K' || $g === '0') {
            return 0;
        }
        if (preg_match('/^-?\d+$/', $g)) {
            return (int) $g;
        }
        return -1;
    }
}

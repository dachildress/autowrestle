<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Wrestler;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WrestlerController extends Controller
{
    protected const GRADES = ['K', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

    public function index(Request $request): View
    {
        $wrestlers = Wrestler::where('user_id', $request->user()->id)
            ->orderBy('wr_last_name')
            ->orderBy('wr_first_name')
            ->get();
        return view('wrestlers.index', compact('wrestlers'));
    }

    public function create(): View
    {
        $clubs = Club::orderBy('Club')->get();
        return view('wrestlers.create', compact('clubs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wr_first_name' => 'required|string|max:30',
            'wr_last_name' => 'required|string|max:30',
            'wr_gender' => 'nullable|string|in:Boy,Girl',
            'wr_club' => 'required|string|max:30',
            'wr_dob' => 'nullable|string',
            'wr_age' => 'nullable|integer|min:3|max:19',
            'wr_grade' => 'required|string|max:10',
            'wr_weight' => 'nullable|numeric|min:0|max:500',
            'wr_years' => 'required|integer|min:0|max:30',
            'usawnumber' => 'nullable|string|max:50',
        ]);
        $validated['user_id'] = $request->user()->id;
        $validated['wr_pr'] = (int) ($validated['wr_weight'] ?? 0);
        $validated['wr_weight'] = $validated['wr_weight'] ?? null;
        $validated['wr_club'] = substr(trim($validated['wr_club']), 0, 30);
        if (! empty($validated['wr_dob']) && is_string($validated['wr_dob'])) {
            try {
                $dob = Carbon::createFromFormat('m/d/Y', trim($validated['wr_dob']));
                $validated['wr_dob'] = $dob->format('Y-m-d');
                $validated['wr_age'] = $dob->age;
            } catch (\Exception $e) {
                unset($validated['wr_dob']);
                $validated['wr_age'] = (int) $request->input('wr_age', 0) ?: 9;
            }
        } else {
            $validated['wr_age'] = (int) $request->input('wr_age', 0) ?: 9;
            $validated['wr_dob'] = null;
        }
        $usa = trim($validated['usawnumber'] ?? '');
        $validated['usawnumber'] = ($usa !== '' && preg_match('/^\d+$/', $usa)) ? (int) $usa : null;
        Wrestler::create($validated);
        return redirect()->route('wrestlers.index')->with('success', 'Wrestler added.');
    }

    public function createClub(): View
    {
        return view('wrestlers.clubs.create');
    }

    public function storeClub(Request $request): RedirectResponse
    {
        $request->validate(['Club' => 'required|string|max:255']);
        $name = trim($request->input('Club'));
        if (strlen($name) > 30) {
            $name = substr($name, 0, 30);
        }
        Club::firstOrCreate(['Club' => $name], ['Club' => $name]);
        return redirect()->route('wrestlers.create')->with('success', 'Club added. Select it below.');
    }

    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $wrestler = Wrestler::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $clubs = Club::orderBy('Club')->get();
        return view('wrestlers.edit', compact('wrestler', 'clubs'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $wrestler = Wrestler::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $validated = $request->validate([
            'wr_first_name' => 'required|string|max:30',
            'wr_last_name' => 'required|string|max:30',
            'wr_gender' => 'nullable|string|in:Boy,Girl',
            'wr_club' => 'required|string|max:30',
            'wr_dob' => 'nullable|string',
            'wr_grade' => 'required|string|max:10',
            'wr_weight' => 'nullable|numeric|min:0|max:500',
            'wr_years' => 'required|integer|min:0|max:30',
            'usawnumber' => 'nullable|string|max:50',
            'coach_name' => 'nullable|string|max:50',
            'coach_phone' => 'nullable|string|max:14',
        ]);
        $validated['wr_club'] = substr(trim($validated['wr_club']), 0, 30);
        if (! empty($validated['wr_dob']) && is_string($validated['wr_dob'])) {
            try {
                $dob = Carbon::createFromFormat('m/d/Y', trim($validated['wr_dob']));
                $validated['wr_dob'] = $dob->format('Y-m-d');
                $validated['wr_age'] = $dob->age;
            } catch (\Exception $e) {
                unset($validated['wr_dob']);
                $validated['wr_age'] = (int) $request->input('wr_age', $wrestler->wr_age);
            }
        } else {
            $validated['wr_age'] = (int) $request->input('wr_age', $wrestler->wr_age);
        }
        $usa = trim($validated['usawnumber'] ?? '');
        $validated['usawnumber'] = ($usa !== '' && preg_match('/^\d+$/', $usa)) ? (int) $usa : null;
        $validated['coach_name'] = trim($validated['coach_name'] ?? '') ?: '';
        $validated['coach_phone'] = trim($validated['coach_phone'] ?? '') ?: '';
        $wrestler->wr_pr = (int) ($validated['wr_weight'] ?? $wrestler->wr_weight ?? 0);
        $wrestler->update($validated);
        return redirect()->route('wrestlers.index')->with('success', 'Wrestler updated.');
    }
}

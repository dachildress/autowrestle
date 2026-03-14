<?php

namespace App\Http\Controllers;

use App\Models\Wrestler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WrestlerController extends Controller
{
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
        return view('wrestlers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wr_first_name' => 'required|string|max:30',
            'wr_last_name' => 'required|string|max:30',
            'wr_club' => 'required|string|max:30',
            'wr_age' => 'required|integer|min:3|max:19',
            'wr_grade' => 'required|string|max:10',
            'wr_weight' => 'nullable|numeric|min:0|max:500',
            'wr_years' => 'required|integer|min:0|max:30',
        ]);
        $validated['user_id'] = $request->user()->id;
        $validated['wr_pr'] = (int) ($validated['wr_weight'] ?? 0);
        $validated['wr_weight'] = $validated['wr_weight'] ?? null;
        Wrestler::create($validated);
        return redirect()->route('wrestlers.index')->with('success', 'Wrestler added.');
    }

    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $wrestler = Wrestler::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        return view('wrestlers.edit', compact('wrestler'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $wrestler = Wrestler::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $validated = $request->validate([
            'wr_first_name' => 'required|string|max:30',
            'wr_last_name' => 'required|string|max:30',
            'wr_club' => 'required|string|max:30',
            'wr_age' => 'required|integer|min:3|max:19',
            'wr_grade' => 'required|string|max:10',
            'wr_weight' => 'nullable|numeric|min:0|max:500',
            'wr_years' => 'required|integer|min:0|max:30',
            'wr_dob' => 'nullable|string',
            'usawnumber' => 'nullable',
            'coach_name' => 'nullable|string|max:50',
            'coach_phone' => 'nullable|string|max:14',
        ]);
        if (! empty($validated['wr_dob']) && is_string($validated['wr_dob'])) {
            try {
                $validated['wr_dob'] = \Carbon\Carbon::createFromFormat('m/d/Y', trim($validated['wr_dob']))->format('Y-m-d');
            } catch (\Exception $e) {
                unset($validated['wr_dob']);
            }
        }
        $wrestler->wr_pr = (int) ($validated['wr_weight'] ?? $wrestler->wr_weight ?? 0);
        $wrestler->update($validated);
        return redirect()->route('wrestlers.index')->with('success', 'Wrestler updated.');
    }
}

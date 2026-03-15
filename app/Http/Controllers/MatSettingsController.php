<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Mat-side settings (display options for virtual/audience scoreboard). Scorer-only.
 */
class MatSettingsController extends Controller
{
    private const SESSION_SHOW_HEAD_NECK = 'mat_display_show_head_neck';
    private const SESSION_SHOW_RECOVER = 'mat_display_show_recover';

    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user->isScorer()) {
            abort(403, 'Only scorer users can access mat settings.');
        }

        // Default false = unchecked = timers hidden; only show when user has checked and saved
        return view('mat.settings', [
            'matNumber' => $user->mat_number,
            'showHeadNeck' => $request->session()->get(self::SESSION_SHOW_HEAD_NECK, false),
            'showRecover' => $request->session()->get(self::SESSION_SHOW_RECOVER, false),
            'currentBoutId' => $request->session()->get('mat_current_bout_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user->isScorer()) {
            abort(403, 'Only scorer users can access mat settings.');
        }

        $request->session()->put(self::SESSION_SHOW_HEAD_NECK, $request->boolean('show_head_neck'));
        $request->session()->put(self::SESSION_SHOW_RECOVER, $request->boolean('show_recover'));

        $boutId = $request->session()->get('mat_current_bout_id');
        $redirect = $boutId
            ? redirect()->route('mat.bout.show', ['boutId' => $boutId])->with('success', 'Settings saved.')
            : redirect()->route('mat.settings')->with('success', 'Settings saved.');

        return $redirect;
    }
}

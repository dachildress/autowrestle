<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Mat-side settings (display, sound). Scorer-only; placeholder/minimal.
 */
class MatSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user->isScorer()) {
            abort(403, 'Only scorer users can access mat settings.');
        }

        return view('mat.settings', [
            'matNumber' => $user->mat_number,
        ]);
    }
}

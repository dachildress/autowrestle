<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManageScorerController extends Controller
{
    private const SCORER_ACCESSLEVEL = '5';

    private function authorizeAdmin(Request $request): void
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only administrators can manage scorer users.');
        }
    }

    /**
     * List scorer users (admin only).
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $scorers = User::where('accesslevel', self::SCORER_ACCESSLEVEL)
            ->orderBy('username')
            ->get();
        return view('manage.scorers.index', ['scorers' => $scorers]);
    }

    /**
     * Show form to create a scorer (admin only).
     */
    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);
        return view('manage.scorers.create');
    }

    /**
     * Store a new scorer user (admin only).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $valid = $request->validate([
            'username' => 'required|string|max:40|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mat_number' => 'nullable|integer|min:1|max:255',
            'active' => 'nullable|in:0,1',
        ]);
        User::create([
            'name' => $valid['username'],
            'username' => $valid['username'],
            'email' => $valid['email'],
            'password' => $valid['password'],
            'accesslevel' => self::SCORER_ACCESSLEVEL,
            'active' => $valid['active'] ?? '1',
            'mat_number' => !empty($valid['mat_number']) ? (int) $valid['mat_number'] : null,
        ]);
        return redirect()->route('manage.scorers.index')->with('success', 'Scorer user created.');
    }

    /**
     * Show form to edit a scorer (admin only).
     */
    public function edit(Request $request, int $id): View
    {
        $this->authorizeAdmin($request);
        $scorer = User::where('accesslevel', self::SCORER_ACCESSLEVEL)->findOrFail($id);
        return view('manage.scorers.edit', ['scorer' => $scorer]);
    }

    /**
     * Update a scorer user (admin only).
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $scorer = User::where('accesslevel', self::SCORER_ACCESSLEVEL)->findOrFail($id);
        $valid = $request->validate([
            'username' => 'required|string|max:40|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'mat_number' => 'nullable|integer|min:1|max:255',
            'active' => 'nullable|in:0,1',
        ]);
        $scorer->username = $valid['username'];
        $scorer->email = $valid['email'];
        $scorer->active = $valid['active'] ?? '1';
        $scorer->mat_number = !empty($valid['mat_number']) ? (int) $valid['mat_number'] : null;
        if (!empty($valid['password'])) {
            $scorer->password = $valid['password'];
        }
        $scorer->save();
        return redirect()->route('manage.scorers.index')->with('success', 'Scorer updated.');
    }

    /**
     * Delete a scorer user (admin only).
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $scorer = User::where('accesslevel', self::SCORER_ACCESSLEVEL)->findOrFail($id);
        $scorer->delete();
        return redirect()->route('manage.scorers.index')->with('success', 'Scorer user removed.');
    }
}

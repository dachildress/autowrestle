<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteContentController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can manage site content.');
        }
    }

    /**
     * List all editable content sections (admin only).
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $sections = config('site_content.sections', []);
        $keys = array_keys($sections);
        $dbValues = SiteContent::whereIn('key', $keys)->pluck('value', 'key')->toArray();

        $items = [];
        foreach ($sections as $key => $config) {
            $items[] = [
                'key' => $key,
                'label' => $config['label'] ?? $key,
                'type' => $config['type'] ?? 'text',
                'value' => $dbValues[$key] ?? $config['default'] ?? '',
                'has_value' => isset($dbValues[$key]) && $dbValues[$key] !== '' && $dbValues[$key] !== null,
            ];
        }

        return view('manage.content.index', ['items' => $items]);
    }

    /**
     * Show form to edit one content section (admin only).
     */
    public function edit(Request $request, string $key): View|RedirectResponse
    {
        $this->authorizeAdmin($request);
        $sections = config('site_content.sections', []);
        if (! isset($sections[$key])) {
            return redirect()->route('manage.content.index')->with('error', 'Unknown content key.');
        }
        $config = $sections[$key];
        $row = SiteContent::find($key);
        $value = $row ? $row->value : ($config['default'] ?? '');

        return view('manage.content.edit', [
            'key' => $key,
            'label' => $config['label'] ?? $key,
            'type' => $config['type'] ?? 'text',
            'value' => $value,
        ]);
    }

    /**
     * Update one content section (admin only).
     */
    public function update(Request $request, string $key): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $sections = config('site_content.sections', []);
        if (! isset($sections[$key])) {
            return redirect()->route('manage.content.index')->with('error', 'Unknown content key.');
        }
        $config = $sections[$key];
        $type = $config['type'] ?? 'text';

        if ($type === 'image') {
            $request->validate([
                'value' => 'nullable|image|max:2048',
            ]);
            $path = null;
            if ($request->hasFile('value')) {
                $file = $request->file('value');
                $old = SiteContent::find($key);
                if ($old && $old->value) {
                    Storage::disk('public')->delete($old->value);
                }
                $path = $file->store('site-content', 'public');
            } else {
                $existing = SiteContent::find($key);
                $path = $existing ? $existing->value : null;
            }
            SiteContent::updateOrCreate(
                ['key' => $key],
                ['type' => 'image', 'value' => $path]
            );
        } else {
            $request->validate([
                'value' => 'nullable|string|max:65535',
            ]);
            $value = $request->input('value', '');
            SiteContent::updateOrCreate(
                ['key' => $key],
                ['type' => 'text', 'value' => $value === '' ? null : $value]
            );
        }

        return redirect()->route('manage.content.index')->with('success', 'Content updated.');
    }
}

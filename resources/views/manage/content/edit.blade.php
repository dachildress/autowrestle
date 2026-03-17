@extends('layouts.autowrestle')

@section('title', 'Edit: ' . $label)
@section('panel_title', 'Edit: ' . $label)

@section('content')
<p><a href="{{ route('manage.content.index') }}" class="text-aw-accent hover:underline">← Back to Site content</a></p>

@if(session('error'))
    <x-alert variant="error" class="mt-4">{{ session('error') }}</x-alert>
@endif

<form method="post" action="{{ route('manage.content.update', ['key' => $key]) }}" enctype="multipart/form-data" class="mt-6 max-w-2xl">
    @csrf

    @if($type === 'image')
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Image</label>
            @if($value)
                <p class="mb-2 text-sm text-slate-500">Current image:</p>
                <img src="{{ \App\Models\SiteContent::imageUrl($key) ?? '' }}?t={{ time() }}" alt="" class="mb-2 max-h-40 rounded border border-slate-200 object-cover">
            @endif
            <input type="file" name="value" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded file:border-0 file:bg-aw-primary file:py-2 file:px-4 file:text-white file:text-sm hover:file:bg-slate-800">
            <p class="mt-1 text-sm text-slate-500">Leave empty to keep the current image. Max 2MB. Recommended: JPEG or PNG.</p>
        </div>
    @else
        <div class="mb-4">
            <label for="value" class="block text-sm font-medium text-slate-700 mb-1">Content</label>
            <textarea name="value" id="value" rows="6" class="block w-full rounded border border-slate-200 px-3 py-2 text-slate-900 shadow-sm focus:border-aw-accent focus:ring-1 focus:ring-aw-accent">{{ old('value', $value) }}</textarea>
        </div>
    @endif

    <div class="flex gap-2">
        <x-button type="submit" variant="primary">Save</x-button>
        <x-button variant="ghost" href="{{ route('manage.content.index') }}">Cancel</x-button>
    </div>
</form>
@endsection

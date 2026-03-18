@extends('layouts.autowrestle')

@section('title', 'Edit mat – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Edit mat</h1>
        <p class="mb-6"><a href="{{ route('manage.mat-setup.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Mat setup</a></p>

        @if($errors->any())
            <ul class="mb-4 text-red-600 text-sm list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('manage.mat-setup.update', [$tournament->id, $mat->id]) }}" class="space-y-5">
            @csrf
            <div>
                <label for="mat_number" class="block text-sm font-medium text-slate-700">Mat number <span class="text-red-600">*</span></label>
                <input type="number" name="mat_number" id="mat_number" value="{{ old('mat_number', $mat->mat_number) }}" min="1" max="255" required class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 py-2 px-3 text-sm text-slate-900">
                @error('mat_number') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Name (optional)</label>
                <input type="text" name="name" id="name" value="{{ old('name', $mat->name) }}" maxlength="100" placeholder="e.g. Mat 1" class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm text-slate-900">
            </div>
            <div>
                <label for="constraint" class="block text-sm font-medium text-slate-700">Constraint (optional)</label>
                <input type="text" name="constraint" id="constraint" value="{{ old('constraint', $mat->constraint) }}" maxlength="100" placeholder="e.g. Elementary only, small mats" class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm text-slate-900">
            </div>
            <div class="flex gap-3 pt-2">
                <x-button type="submit" variant="primary">Update</x-button>
                <a href="{{ route('manage.mat-setup.index', $tournament->id) }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </x-card>
</div>
@endsection

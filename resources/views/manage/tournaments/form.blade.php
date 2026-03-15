@extends('layouts.autowrestle')

@section('title', $tournament ? 'Edit Tournament – ' . $tournament->TournamentName : 'Create Tournament')
@section('panel_title', $tournament ? 'Edit Tournament' : 'Create Tournament')

@section('content')
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())
    <ul class="error-list">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
@endif

@if($tournament)
    <p><a href="{{ route('manage.view.summary', $tournament->id) }}">← Back to tournament</a></p>
@else
    <p class="mb-4 text-slate-600">After creation, default divisions and groups (with period times) will be added. You can edit them from the tournament summary.</p>
    @if(!auth()->user()->isAdmin())
        <p class="mb-4 rounded bg-amber-50 p-3 text-sm text-amber-800">Your tournament will need to be approved by an administrator before it appears on the public site. You can still manage it here.</p>
    @endif
@endif

<form method="post" action="{{ $tournament ? route('manage.tournaments.update', $tournament->id) : route('manage.tournaments.store') }}" enctype="multipart/form-data" class="space-y-4 max-w-xl">
    @csrf
    <div>
        <label for="TournamentName" class="block text-sm font-medium text-slate-700">Name <span class="text-red-600">*</span></label>
        <input type="text" name="TournamentName" id="TournamentName" value="{{ old('TournamentName', $tournament?->TournamentName) }}" maxlength="100" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
    </div>
    <div>
        <label for="TournamentDate" class="block text-sm font-medium text-slate-700">Tournament Date <span class="text-red-600">*</span></label>
        <input type="date" name="TournamentDate" id="TournamentDate" value="{{ old('TournamentDate', $tournament?->TournamentDate?->format('Y-m-d')) }}" required class="mt-1 block w-full max-w-xs rounded-md border-slate-300 text-sm">
    </div>
    <div>
        <label for="OpenDate" class="block text-sm font-medium text-slate-700">Tournament Open Date <span class="text-red-600">*</span></label>
        <input type="date" name="OpenDate" id="OpenDate" value="{{ old('OpenDate', $tournament?->OpenDate?->format('Y-m-d')) }}" required class="mt-1 block w-full max-w-xs rounded-md border-slate-300 text-sm">
        @if(!$tournament)
            <p class="mt-1 text-xs text-slate-500">The tournament will not appear on the home page before this date.</p>
        @endif
    </div>
    <div>
        <label for="message" class="block text-sm font-medium text-slate-700">Message</label>
        <textarea name="message" id="message" rows="4" class="mt-1 block w-full rounded-md border-slate-300 text-sm">{{ old('message', $tournament?->message) }}</textarea>
    </div>
    <div>
        <label for="flyer" class="block text-sm font-medium text-slate-700">Flyer</label>
        <input type="file" name="flyer" id="flyer" accept=".pdf" class="mt-1 block w-full max-w-md text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-aw-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-white file:hover:bg-aw-primary/90">
        @if($tournament && $tournament->link)
            <p class="mt-1 text-xs text-slate-500">Current file: {{ $tournament->link }}. Upload a new PDF to replace.</p>
        @else
            <p class="mt-1 text-xs text-slate-500">No file chosen. Upload a PDF to add a flyer.</p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="AllowDouble" id="AllowDouble" value="1" {{ old('AllowDouble', $tournament?->AllowDouble ?? '1') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-aw-primary">
        <label for="AllowDouble" class="text-sm font-medium text-slate-700">Allow Double Brackets</label>
        <span class="text-slate-500">YES</span>
    </div>
    <p class="text-xs text-slate-500">When enabled, wrestlers can register for 2 brackets when they sign up for this tournament.</p>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="ViewWrestlers" id="ViewWrestlers" value="1" {{ old('ViewWrestlers', $tournament?->ViewWrestlers ?? 0) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-aw-primary">
        <label for="ViewWrestlers" class="text-sm font-medium text-slate-700">Show Registered Wrestlers</label>
        <span class="text-slate-500">YES</span>
    </div>
    <p class="text-xs text-slate-500">When enabled, users can see the list of registered wrestlers from the tournament page and home page.</p>
    <div class="flex gap-3 pt-2">
        <button type="submit" class="inline-flex items-center rounded-md bg-aw-primary px-4 py-2 text-sm font-medium text-white hover:bg-aw-primary/90">{{ $tournament ? 'Save' : 'Create Tournament' }}</button>
        <a href="{{ $tournament ? route('manage.view.summary', $tournament->id) : route('manage.tournaments.index') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ $tournament ? 'Cancel' : 'Cancel' }}</a>
    </div>
</form>
@endsection

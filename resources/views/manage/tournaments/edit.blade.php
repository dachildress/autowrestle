@extends('layouts.autowrestle')

@section('title', 'Edit Tournament – ' . $tournament->TournamentName)
@section('panel_title', 'Edit Tournament')

@section('content')
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())
    <ul class="error-list">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
@endif

<p><a href="{{ route('manage.view.summary', $tournament->id) }}">← Back to tournament</a></p>

<form method="post" action="{{ route('manage.tournaments.update', $tournament->id) }}" enctype="multipart/form-data" class="form-horizontal">
    @csrf
    <div class="form-group">
        <label for="TournamentName">Name:</label>
        <input type="text" name="TournamentName" id="TournamentName" value="{{ old('TournamentName', $tournament->TournamentName) }}" maxlength="100" required class="form-control" style="max-width: 400px;">
    </div>
    <div class="form-group">
        <label for="TournamentDate">Tournament Date:</label>
        <input type="date" name="TournamentDate" id="TournamentDate" value="{{ old('TournamentDate', $tournament->TournamentDate?->format('Y-m-d')) }}" required class="form-control" style="max-width: 200px;">
    </div>
    <div class="form-group">
        <label for="OpenDate">Tournament Open Date:</label>
        <input type="date" name="OpenDate" id="OpenDate" value="{{ old('OpenDate', $tournament->OpenDate?->format('Y-m-d')) }}" required class="form-control" style="max-width: 200px;">
    </div>
    <div class="form-group">
        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="4" class="form-control" style="max-width: 500px;">{{ old('message', $tournament->message) }}</textarea>
    </div>
    <div class="form-group">
        <label for="flyer">Flyer:</label>
        <input type="file" name="flyer" id="flyer" accept=".pdf" class="form-control" style="max-width: 400px;">
        @if($tournament->link)
            <p class="help">Current file: {{ $tournament->link }}. Upload a new PDF to replace.</p>
        @else
            <p class="help">No file chosen. Upload a PDF to add a flyer.</p>
        @endif
    </div>
    <div class="form-group">
        <label>Allow Double Brackets:</label>
        <label class="toggle-label">
            <input type="checkbox" name="AllowDouble" value="1" {{ old('AllowDouble', $tournament->AllowDouble) ? 'checked' : '' }}>
            <span class="toggle-text">YES</span>
        </label>
    </div>
    <div class="form-group">
        <label>Show Registered Wrestlers:</label>
        <label class="toggle-label">
            <input type="checkbox" name="ViewWrestlers" value="1" {{ old('ViewWrestlers', $tournament->ViewWrestlers) ? 'checked' : '' }}>
            <span class="toggle-text">YES</span>
        </label>
    </div>
    <div class="form-actions" style="margin-top: 1rem;">
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('manage.view.summary', $tournament->id) }}" class="btn btn-danger">Cancel</a>
    </div>
</form>

<style>
.toggle-label { display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; }
.toggle-label input[type="checkbox"] { width: 2.5rem; height: 1.25rem; accent-color: #3498db; }
.help { margin: 0.25rem 0 0; font-size: 0.9rem; color: #7f8c8d; }
</style>
@endsection

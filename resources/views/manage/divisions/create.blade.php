@extends('layouts.autowrestle')

@section('title', 'Add Division – ' . $tournament->TournamentName)

@section('content')
<h1>Add Division</h1>
<p><a href="{{ route('manage.divisions.index', $tournament->id) }}">← Back to divisions</a></p>

<form method="post" action="{{ route('manage.divisions.store', $tournament->id) }}">
    @csrf
    <p>
        <label for="DivisionName">Name</label>
        <input type="text" name="DivisionName" id="DivisionName" value="{{ old('DivisionName') }}" maxlength="45" required>
        @error('DivisionName') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="StartingMat">Starting Mat #</label>
        <input type="number" name="StartingMat" id="StartingMat" value="{{ old('StartingMat') }}" min="0" required>
        @error('StartingMat') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="TotalMats">Total Mats</label>
        <input type="number" name="TotalMats" id="TotalMats" value="{{ old('TotalMats') }}" min="0" required>
        @error('TotalMats') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="PerBracket">Wrestlers Per Bracket</label>
        <input type="number" name="PerBracket" id="PerBracket" value="{{ old('PerBracket') }}" min="0" required>
        @error('PerBracket') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <button type="submit">Save</button>
        <a href="{{ route('manage.divisions.index', $tournament->id) }}">Cancel</a>
    </p>
</form>
@endsection

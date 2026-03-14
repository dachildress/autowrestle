@extends('layouts.autowrestle')

@section('title', 'Score bout – ' . $tournament->TournamentName)

@section('content')
<h1>Score a bout</h1>
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>

<form action="{{ route('manage.scoring.show', $tournament->id) }}" method="post">
    @csrf
    <p>
        <label for="bout">Bout #</label>
        <input type="number" name="bout" id="bout" value="{{ old('bout') }}" min="1" required>
        @error('bout') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <button type="submit">Find bout & score</button>
    </p>
</form>
@endsection

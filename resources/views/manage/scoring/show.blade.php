@extends('layouts.autowrestle')

@section('title', 'Score bout #' . $boutId . ' – ' . $tournament->TournamentName)

@section('content')
<h1>Score bout #{{ $boutId }}</h1>
<p><a href="{{ route('manage.scoring.index', $tournament->id) }}">← Back to score entry</a></p>

<p><strong>{{ $wr1->wr_first_name }} {{ $wr1->wr_last_name }}</strong> — {{ $wr1->wr_club }}</p>
<p><strong>{{ $wr2->wr_first_name }} {{ $wr2->wr_last_name }}</strong> — {{ $wr2->wr_club }}</p>

<form action="{{ route('manage.scoring.update', $tournament->id) }}" method="post">
    @csrf
    <input type="hidden" name="bout_id" value="{{ $boutId }}">
    <p>
        <label for="points1">Points (wrestler 1)</label>
        <input type="number" name="points1" id="points1" value="{{ old('points1', $boutRows[0]->points ?? 0) }}" min="0" step="0.1">
    </p>
    <p>
        <label for="points2">Points (wrestler 2)</label>
        <input type="number" name="points2" id="points2" value="{{ old('points2', $boutRows[1]->points ?? 0) }}" min="0" step="0.1">
    </p>
    <p>
        <label for="wintype">Win type</label>
        <select name="wintype" id="wintype">
            <option value="Points" {{ old('wintype', 'Points') === 'Points' ? 'selected' : '' }}>Points</option>
            <option value="Fall" {{ old('wintype') === 'Fall' ? 'selected' : '' }}>Fall</option>
            <option value="Forfeit" {{ old('wintype') === 'Forfeit' ? 'selected' : '' }}>Forfeit</option>
            <option value="Disqualified" {{ old('wintype') === 'Disqualified' ? 'selected' : '' }}>Disqualified</option>
            <option value="Double DQ" {{ old('wintype') === 'Double DQ' ? 'selected' : '' }}>Double DQ</option>
            <option value="Double FF" {{ old('wintype') === 'Double FF' ? 'selected' : '' }}>Double FF</option>
        </select>
    </p>
    <p>
        <label for="totaltime">Time</label>
        <input type="text" name="totaltime" id="totaltime" value="{{ old('totaltime', $boutRows[0]->wrtime ?? '0:00') }}" maxlength="5" size="5" placeholder="0:00">
    </p>
    <p>
        <button type="submit">Save score</button>
        <a href="{{ route('manage.scoring.index', $tournament->id) }}">Cancel</a>
    </p>
</form>
@endsection

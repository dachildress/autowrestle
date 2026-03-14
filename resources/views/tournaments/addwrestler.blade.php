@extends('layouts.autowrestle')

@section('title', 'Add Wrestler – ' . $tournament->TournamentName)

@section('content')
<h1>{{ $tournament->TournamentName }}</h1>
<p>Date: {{ $tournament->TournamentDate->format('D M j, Y') }}</p>
<p><strong>{{ $wrestler->wr_first_name }} {{ $wrestler->wr_last_name }}</strong> — Age: {{ $wrestler->wr_age }} | Grade: {{ $wrestler->wr_grade }} | Yrs Exp: {{ $wrestler->wr_years }}</p>

<form action="{{ route('tournaments.register.insert', ['wid' => $wrestler->id, 'tid' => $tournament->id]) }}" method="post" id="register-form">
    @csrf
    <input type="hidden" name="brackets" id="brackets" value="1">

    @if($errors->has('group'))
        <p class="error">{{ $errors->first('group') }}</p>
    @endif

    <p>
        <label for="wr_weight">Weight</label>
        <input type="number" name="wr_weight" id="wr_weight" value="{{ old('wr_weight', $wrestler->wr_weight) }}" min="1" max="500" step="0.1" required>
        @if($errors->has('wr_weight'))
            <span class="error">{{ $errors->first('wr_weight') }}</span>
        @endif
    </p>

    <p>
        <button type="button" name="add1" onclick="submitBrackets(1)">Register for 1 Bracket</button>
    </p>
    @if($tournament->AllowDouble == '1' || $tournament->AllowDouble === true)
    <p>
        <button type="button" name="add2" onclick="submitBrackets(2)">Register for 2 Brackets</button>
    </p>
    @endif
    <p>
        <a href="{{ route('tournaments.register', $tournament->id) }}">Cancel</a>
    </p>
</form>

<script>
function submitBrackets(num) {
    document.getElementById('brackets').value = String(num);
    document.getElementById('register-form').submit();
}
</script>
@endsection

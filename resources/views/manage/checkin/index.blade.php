@extends('layouts.autowrestle')

@section('title', 'Check-in – ' . $tournament->TournamentName)

@section('content')
<h1>Check-in</h1>
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>

<h2>Select a group</h2>
@if(empty($groups))
    <p>No groups. Add divisions and groups first.</p>
@else
    <ul class="group-tabs">
        @foreach($groups as $g)
            <li><a href="{{ route('manage.checkin.show', [$tournament->id, $g->division_id, $g->id]) }}">{{ $g->division_name }} – {{ $g->Name }}</a></li>
        @endforeach
    </ul>
    <h2>Print check-in by division</h2>
    <ul class="group-tabs">
        @foreach($tournament->divisions as $div)
            <li><a href="{{ route('manage.checkin.print', [$tournament->id, $div->id]) }}" target="_blank">{{ $div->DivisionName }}</a></li>
        @endforeach
    </ul>
@endif
@endsection

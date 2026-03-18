@extends('layouts.autowrestle')

@section('title', 'My Bouts – ' . $tournament->TournamentName)
@section('panel_title', 'My Bouts')

@section('content')
<p><strong>{{ $wrestler->wr_first_name }} {{ $wrestler->wr_last_name }}</strong> — {{ $tournament->TournamentName }}</p>

@if(empty($data))
    <p>No bouts yet.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Bout</th>
                <th>Opponent</th>
                <th>Club</th>
                <th style="text-align: center;">Score</th>
                <th style="text-align: center;">Pin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row->bout_number ?? $row->bout_id }}</td>
                    <td>{{ $row->opponent_name }}</td>
                    <td>{{ $row->opponent_club }}</td>
                    <td style="text-align: center;">{{ $row->score }}</td>
                    <td style="text-align: center;">@if($row->pin) ✓ @else — @endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<p style="margin-top: 1rem;"><a href="{{ route('mybouts.search', $tid) }}">Search for another wrestler</a> | <a href="{{ route('tournaments.show', $tid) }}">Back to tournament</a></p>
@endsection

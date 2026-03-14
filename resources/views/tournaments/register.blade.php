@extends('layouts.autowrestle')

@section('title', 'Register – ' . $tournament->TournamentName)

@section('content')
<h1>{{ $tournament->TournamentName }}</h1>
<p><strong>Date:</strong> {{ $tournament->TournamentDate->format('l, F j, Y') }}</p>
@if($tournament->link)
    <p><a href="{{ url('flyers/' . $tournament->link) }}" target="_blank" rel="noopener">Tournament Flyer</a></p>
@endif

<h2>My Wrestlers</h2>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Club</th>
            <th>Age</th>
            <th>Grade</th>
            <th>Weight</th>
            <th>Years Exp</th>
            <th>Wins</th>
            <th>Losses</th>
            <th>Status</th>
            <th>Options</th>
        </tr>
    </thead>
    <tbody>
        @foreach($wrestlers as $wrestler)
        @php $status = $statusByWrestler[$wrestler->id] ?? 'add'; @endphp
        <tr>
            <td>{{ $wrestler->wr_first_name }} {{ $wrestler->wr_last_name }}</td>
            <td>{{ $wrestler->wr_club }}</td>
            <td>{{ $wrestler->wr_age }}</td>
            <td>{{ $wrestler->wr_grade }}</td>
            <td>{{ $wrestler->wr_weight }}</td>
            <td>{{ $wrestler->wr_years }}</td>
            <td>{{ $wrestler->wr_wins }}</td>
            <td>{{ $wrestler->wr_losses }}</td>
            <td>
                @if($status === 'locked')
                    Registered
                @elseif($status === 'withdraw')
                    Registered
                @else
                    Not Registered
                @endif
            </td>
            <td>
                @if($status === 'locked')
                    <a href="{{ route('tournaments.register.locked') }}">Locked</a>
                @elseif($status === 'withdraw')
                    <a href="{{ route('tournaments.register.withdraw', ['wid' => $wrestler->id, 'tid' => $tournament->id]) }}">Withdraw</a>
                @else
                    <a href="{{ route('tournaments.register.add', ['wid' => $wrestler->id, 'tid' => $tournament->id]) }}">Add Wrestler</a>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<p><a href="{{ route('tournaments.show', $tournament->id) }}">Back to tournament</a></p>
@endsection

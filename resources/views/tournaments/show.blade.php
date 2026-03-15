@extends('layouts.autowrestle')

@section('title', $tournament->TournamentName)
@section('panel_title', $tournament->TournamentName . ' — ' . $tournament->TournamentDate->format('m-d-Y'))

@section('content')
<p><strong>Date:</strong> {{ $tournament->TournamentDate->format('l, F j, Y') }}</p>
@if($tournament->link)
    <p><a href="{{ asset('flyers/' . $tournament->link) }}" target="_blank" rel="noopener" class="text-aw-accent hover:underline">Tournament Flyer</a></p>
@endif
@auth
    @if((int) $tournament->status !== 2)
        <p><a href="{{ route('tournaments.register', $tournament->id) }}" class="btn btn-block">Register for this tournament</a></p>
    @endif
@endauth
<p><a href="{{ route('mybouts.search', $tournament->id) }}">Search for your wrestler / Find your bouts</a></p>
@if($tournament->message)
    <div style="white-space: pre-wrap;">{{ $tournament->message }}</div>
@endif

<h2 style="margin-top:1rem;">Divisions</h2>
@if($tournament->divisions->isEmpty())
    <p>No divisions.</p>
@else
    <ul>
        @foreach($tournament->divisions as $div)
            <li>{{ $div->DivisionName }}</li>
        @endforeach
    </ul>
@endif

@if($tournament->ViewWrestlers)
<h2 style="margin-top:1rem;">Registered Wrestlers</h2>
@if($tournament->tournamentWrestlers->isEmpty())
    <p>No wrestlers registered yet.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Club</th>
                <th>Weight</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tournament->tournamentWrestlers as $tw)
            <tr>
                <td>{{ $tw->full_name }}</td>
                <td>{{ $tw->wr_club }}</td>
                <td>{{ $tw->wr_weight ?? '–' }}</td>
                <td>{{ $tw->wr_grade }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@else
<p class="mt-4 text-slate-600">Registered wrestlers are not displayed for this tournament.</p>
@endif
@endsection

@extends('layouts.autowrestle')

@section('title', 'Print Bouts – ' . $tournament->TournamentName)

@section('content')
<h1>Print bout sheets</h1>
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>

@foreach($tournament->divisions as $division)
    <div style="margin-bottom: 1rem;">
        <h3>{{ $division->DivisionName }}</h3>
        @if($division->bouted)
            <p>
                <a href="{{ route('manage.bouts.print', [$tournament->id, $division->id]) }}" target="_blank">Print all rounds</a>
                @for($r = 1; $r <= 5; $r++)
                    | <a href="{{ route('manage.bouts.print', [$tournament->id, $division->id, $r]) }}" target="_blank">Round {{ $r }}</a>
                @endfor
            </p>
        @else
            <p>Bouts have not been created for this division.</p>
        @endif
    </div>
@endforeach
@endsection

@extends('layouts.autowrestle')

@section('title', 'Manage: ' . $tournament->TournamentName)

@section('content')
<h1>Manage: {{ $tournament->TournamentName }}</h1>
<p>Date: {{ $tournament->TournamentDate->format('M j, Y') }}</p>
@if(session('success'))<p class="text-green-700">{{ session('success') }}</p>@endif
@if(session('error'))<p class="text-red-700">{{ session('error') }}</p>@endif

<p class="mb-4">
    <a href="{{ route('manage.number-schemes.index', $tournament->id) }}" class="inline-flex items-center rounded-md bg-aw-accent px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90">Number Schemes</a>
</p>

<h2>Divisions &amp; Groups</h2>
<p>
    <a href="{{ route('manage.divisions.index', $tournament->id) }}">Manage divisions and groups</a>
    | <a href="{{ route('manage.number-schemes.index', $tournament->id) }}"><strong>Number Schemes</strong></a>
    | <a href="{{ route('manage.scoring.index', $tournament->id) }}">Score a bout</a>
    | <a href="{{ route('manage.checkin.index', $tournament->id) }}">Check-in</a>
    @if($tournament->divisions->where('bouted', 1)->isNotEmpty())
        | <a href="{{ route('manage.bouts.selectPrint', $tournament->id) }}">Print bout sheets</a>
    @endif
</p>
@foreach($tournament->divisions as $div)
    <h3>{{ $div->DivisionName }}</h3>
    @if($div->divGroups->isEmpty())
        <p>No groups.</p>
    @else
        <ul>
            @foreach($div->divGroups as $g)
                <li>
                    {{ $g->Name }} (BracketType: {{ $g->BracketType }})
                    @if($div->Bracketed && $g->bracketed)
                                — <a href="{{ route('manage.brackets.show', [$tournament->id, $div->id, $g->id]) }}">Show brackets</a>
                    @endif
                </li>
            @endforeach
        </ul>
        @if(!$div->Bracketed)
            <p><a href="{{ route('manage.brackets.create', [$tournament->id, $div->id]) }}" onclick="return confirm('Create brackets for {{ $div->DivisionName }}?');">Create brackets</a> for this division.</p>
        @else
            <p>
                <a href="{{ route('manage.brackets.show', [$tournament->id, $div->id, $div->divGroups->first()?->id]) }}">View brackets</a>
                | <a href="{{ route('manage.brackets.unbracket', [$tournament->id, $div->id]) }}" onclick="return confirm('Clear all brackets and bouts for {{ $div->DivisionName }}?');">Unbracket</a>
                @if(!$div->bouted)
                    @if($divisionHasScheme[$div->id] ?? false)
                        | <a href="{{ route('manage.bouts.create', [$tournament->id, $div->id]) }}" class="js-create-bouts" data-confirm="Create bouts for {{ $div->DivisionName }}?" data-division-name="{{ $div->DivisionName }}">Create bouts</a>
                    @else
                        | <span class="text-slate-500">No Scheme</span>
                    @endif
                @else
                    | <a href="{{ route('manage.bouts.unbout', [$tournament->id, $div->id]) }}" onclick="return confirm('Clear bouts for {{ $div->DivisionName }}?');">Unbout</a>
                    | <a href="{{ route('manage.bouts.print', [$tournament->id, $div->id]) }}" target="_blank">Print bouts</a>
                @endif
            </p>
        @endif
    @endif
@endforeach

<h2>Tournament Wrestlers</h2>
<p>{{ $tournament->tournamentWrestlers->count() }} registered.</p>
@endsection

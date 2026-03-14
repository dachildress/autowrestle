@extends('layouts.autowrestle')

@section('title', 'View Groups – ' . $group->Name . ' – ' . $tournament->TournamentName)

@section('content')
<div style="text-align: center; margin-bottom: 1rem;">
    <h2 style="margin: 0;">{{ $tournament->TournamentName }} {{ $tournament->TournamentDate->format('m-d-Y') }}</h2>
</div>

<p style="margin-bottom: 0.5rem;">All groups (all divisions):</p>
<ul class="viewgroups-tabs">
    @foreach($allGroups as $g)
        <li class="{{ $g->did === $division->id && $g->gid === $group->id ? 'active' : '' }}">
            @if($g->did === $division->id && $g->gid === $group->id)
                <strong>{{ $g->division_name }}: {{ $g->name }}</strong>
            @else
                <a href="{{ route('manage.viewgroups.show', [$tournament->id, $g->did, $g->gid]) }}">{{ $g->division_name }}: {{ $g->name }}</a>
            @endif
        </li>
    @endforeach
</ul>

<h2>Wrestlers</h2>
@if($wrestlers->isEmpty())
    <p>No wrestlers in this group.</p>
@else
    <table class="table-dark">
        <thead>
            <tr>
                <th>Name</th>
                <th>Club</th>
                <th style="text-align: center;">Age</th>
                <th style="text-align: center;">Grade</th>
                <th style="text-align: center;">Weight</th>
                <th style="text-align: center;">Years</th>
                <th style="text-align: center;">Options</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wrestlers as $w)
            <tr>
                <td>{{ $w->wr_first_name }} {{ $w->wr_last_name }}</td>
                <td>{{ $w->wr_club }}</td>
                <td style="text-align: center;">{{ $w->wr_age }}</td>
                <td style="text-align: center;">{{ $w->wr_grade }}</td>
                <td style="text-align: center;">{{ $w->wr_weight }}</td>
                <td style="text-align: center;">{{ $w->wr_years }}</td>
                <td style="text-align: center;">
                    @if(!$w->bracketed)
                        <a href="{{ route('manage.brackets.moveWrestlerForm', [$tournament->id, $w->id]) }}?return={{ urlencode(route('manage.viewgroups.show', [$tournament->id, $division->id, $group->id])) }}" title="Move wrestler to another group" aria-label="Move to group">→</a>
                        &nbsp;
                    @endif
                    <a href="{{ route('manage.viewgroups.editWrestler', [$tournament->id, $w->id]) }}" title="Edit wrestler" aria-label="Edit">✎</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

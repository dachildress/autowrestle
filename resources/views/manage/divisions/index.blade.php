@extends('layouts.autowrestle')

@section('title', 'Divisions – ' . $tournament->TournamentName)

@section('content')
<h1>Divisions</h1>
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>
<p><a href="{{ route('manage.divisions.create', $tournament->id) }}">Add New Division</a></p>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th style="text-align: center;">Start Mat #</th>
            <th style="text-align: center;">Total Mats</th>
            <th style="text-align: center;">Wrestlers Per Bracket</th>
            <th style="text-align: center;">Options</th>
        </tr>
    </thead>
    <tbody>
        @foreach($divisions as $div)
        <tr>
            <td>{{ $div->DivisionName }}</td>
            <td style="text-align: center;">{{ $div->StartingMat }}</td>
            <td style="text-align: center;">{{ $div->TotalMats }}</td>
            <td style="text-align: center;">{{ $div->PerBracket }}</td>
            <td style="text-align: center;">
                <a href="{{ route('manage.divisions.show', [$tournament->id, $div->id]) }}" class="btn">Edit</a>
                <a href="{{ route('manage.divisions.destroy', [$tournament->id, $div->id]) }}" class="btn" onclick="return confirm('Delete this division?');">Delete</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<p><em>Select Edit to view and edit the division and its groups.</em></p>
@endsection

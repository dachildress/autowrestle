@extends('layouts.autowrestle')

@section('title', 'View Groups – ' . $tournament->TournamentName)

@section('content')
<h1>View Groups</h1>
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>
<p>No groups yet. Add divisions and groups first.</p>
@if($divisions->isNotEmpty())
    <p><a href="{{ route('manage.divisions.index', $tournament->id) }}">Edit Divisions</a></p>
@endif
@endsection
